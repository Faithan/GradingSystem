<?php

namespace App\Http\Controllers;

use App\Models\Percentage;
use App\Models\QuizzesAndScores;
use App\Models\Classes_Student;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;


class RegistrarController extends Controller
{
    public function index()
    {
        return view("registrar.registrar_dashboard");
    }

    public function registrar_classes()
    {
        $classes = Classes::all();
        $instructors = User::where('role', 'instructor')->get(); // Fetch all class data
        return view('registrar.registrar_classes', compact('classes', 'instructors'));
    }

    public function CreateClass(Request $request)
    {
        $request->validate([
            "subject_code" => "required",
            "descriptive_title" => "required",
            "instructor" => "required",
            "academic_period" => "required",
            "schedule" => "required",
            "status" => "required",
        ]);

        $class = new Classes();
        $class->subject_code = $request->subject_code;
        $class->descriptive_title = $request->descriptive_title;
        $class->instructor = $request->instructor;
        $class->academic_period = $request->academic_period;
        $class->schedule = $request->schedule;
        $class->status = $request->status;

        if ($class->save()) {
            return redirect(route("registrar_classes"))->with("success", "Class Created Successfully");
        }

        return redirect(route("registrar_classes"))->with("error", "Class Creation Failed");
    }

    public function EditClass(Request $request, Classes $class)
    {
        $request->validate([
            "subject_code" => "required",
            "descriptive_title" => "required",
            "instructor" => "required",
            "academic_period" => "required",
            "schedule" => "required",
            "status" => "required",
        ]);

        $class->subject_code = $request->subject_code;
        $class->descriptive_title = $request->descriptive_title;
        $class->instructor = $request->instructor;
        $class->academic_period = $request->academic_period;
        $class->schedule = $request->schedule;
        $class->status = $request->status;

        if ($class->save()) {
            return redirect(route("registrar_classes"))->with("success", "Class Edited Successfully");
        }

        return redirect(route("registrar_classes"))->with("error", "Class Edition Failed");
    }

    public function DeleteClass(Request $request, Classes $class)
    {
        try {
            $class->delete(); // Delete the class from the database
            return redirect()->route('registrar_classes')->with('success', 'Class deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('registrar_classes')->with('error', 'Failed to delete class. Please try again.');
        }
    }

    public function show(Classes $class)
    {
        // Get all student IDs already in the class
        $enrolledStudentIds = Classes_Student::where('classID', $class->id)->pluck('studentID')->toArray();

        // Get students who are not already enrolled in the class
        $students = User::where('role', 'student')->whereNotIn('id', $enrolledStudentIds)->get();

        $classes_student = Classes_Student::where('classID', $class->id)->get();

        $quizzesandscores = QuizzesAndScores::where('classID', $class->id)->get();

        $percentage = Percentage::where('classID', $class->id)->first();


        return view('registrar.registrar_classes_view', compact('class', 'students', 'classes_student', 'quizzesandscores', 'percentage'));
    }

    public function addstudent(Request $request, Classes $class)
    {
        $request->validate([
            "student_id" => "required",
            "name" => "required",
            "email" => "required|email",
            "department" => "required",
        ]);

        // Create a new instance of Classes_Student and assign the values
        $classStudent = new Classes_Student();
        $classStudent->classID = $class->id;
        $classStudent->studentID = $request->student_id;
        $classStudent->name = $request->name;
        $classStudent->email = $request->email;
        $classStudent->department = $request->department;

        // Array of periodic terms
        $periodicTerms = ['Prelim', 'Midterm', 'Semi-Finals', 'Finals'];

        // Save the instance of Classes_Student
        if ($classStudent->save()) {
            // Insert a row for each periodic term in quizzes_scores
            foreach ($periodicTerms as $term) {
                $quizzesandscores = new QuizzesAndScores();
                $quizzesandscores->classID = $class->id;
                $quizzesandscores->studentID = $request->student_id;
                $quizzesandscores->periodic_term = $term;
                $quizzesandscores->save();
            }

            return redirect()->route("class.show", $class->id)->with("success", "Student added successfully.");
        }

        return redirect()->route("class.show", $class->id)->with("error", "Failed to add student. Please try again.");
    }


    public function removestudent($class, $student)
    {
        // Find the student in the class
        $classStudent = Classes_Student::where('classID', $class)
                                    ->where('studentID', $student)
                                    ->first();

        // Find all related quizzes and scores for this student in the class
        $quizzesScores = QuizzesAndScores::where('classID', $class)
                                        ->where('studentID', $student)
                                        ->get();  // Get all records instead of first()

        if ($classStudent || $quizzesScores->isNotEmpty()) {
            if ($classStudent) {
                $classStudent->delete();
            }

            if ($quizzesScores->isNotEmpty()) {
                foreach ($quizzesScores as $score) {
                    $score->delete();  // Delete each record individually
                }
            }

            return redirect()->route("class.show", $class)->with("success", "Student removed successfully.");
        }

        return redirect()->route("class.show", $class)->with("error", "Student not found or already removed.");
    }

    public function addPercentageAndScores(Request $request, Classes $class)
    {
            // Validate the request
        $request->validate([
            'quiz_percentage' => 'required|integer|min:0|max:100',
            'quiz_total_score' => 'nullable|integer|min:0',
            'attendance_percentage' => 'required|integer|min:0|max:100',
            'attendance_total_score' => 'nullable|integer|min:0',
            'assignment_participation_project_percentage' => 'required|integer|min:0|max:100',
            'assignment_participation_project_total_score' => 'nullable|integer|min:0',
            'exam_percentage' => 'required|integer|min:0|max:100',
            'exam_total_score' => 'nullable|integer|min:0',
        ]);

        // Calculate the total percentage
        $totalPercentage = $request->input('quiz_percentage') +
                        $request->input('attendance_percentage') +
                        $request->input('assignment_participation_project_percentage') +
                        $request->input('exam_percentage');

        // Check if total percentage is exactly 100
        if ($totalPercentage !== 100) {
            return redirect()->route("class.show", $class)
                            ->withErrors(['The total percentage must equal 100%.']);
        }

        // Save or update the record in your `percentage` table
        Percentage::updateOrCreate(
            ['classID' => $class->id], // Condition to check if it already exists for this class
            [
                'classID' => $class->id,  // Ensure classID is set in case a new record is created
                'quiz_percentage' => $request->input('quiz_percentage'),
                'quiz_total_score' => $request->input('quiz_total_score'),
                'attendance_percentage' => $request->input('attendance_percentage'),
                'attendance_total_score' => $request->input('attendance_total_score'),
                'assignment_participation_project_percentage' => $request->input('assignment_participation_project_percentage'),
                'assignment_participation_project_total_score' => $request->input('assignment_participation_project_total_score'),
                'exam_percentage' => $request->input('exam_percentage'),
                'exam_total_score' => $request->input('exam_total_score'),
            ]
        );


        return redirect()->route("class.show", $class)->with('success', 'Data saved successfully.');
    }

    public function addQuizAndScore(Request $request, $class)
    {
        $scores = $request->input('scores');
        $periodicTerm = $request->input('periodic_term');

        // Retrieve total scores from the percentage table for the specific class
        $percentage = Percentage::where('classID', $class)->first();

        if (!$percentage) {
            return redirect()->back()->with('error', 'Percentage data not found for this class.');
        }

        foreach ($scores as $studentId => $fields) {
            $classStudent = Classes_Student::where('classID', $class)
                                   ->where('studentID', $studentId)
                                   ->first();

            $studentName = $classStudent->name ?? "Student ID $studentId";// Fetch the student record

            // Validate scores against total scores from percentage table
            if (($fields['quizzez'] ?? 0) > $percentage->quiz_total_score) {
                return redirect()->back()->with('error', "Quiz score for {$studentName} exceeds the total score.");
            }
            if (($fields['attendance_behavior'] ?? 0) > $percentage->attendance_total_score) {
                return redirect()->back()->with('error', "Attendance score for {$studentName} exceeds the total score.");
            }
            if (($fields['assignments'] ?? 0) > $percentage->assignment_participation_project_total_score) {
                return redirect()->back()->with('error', "Assignment score for {$studentName} exceeds the total score.");
            }
            if (($fields['exam'] ?? 0) > $percentage->exam_total_score) {
                return redirect()->back()->with('error', "Exam score for {$studentName} exceeds the total score.");
            }

            // Check for existing record
            $existingRecord = QuizzesAndScores::where('classID', $class)
                ->where('studentID', $studentId)
                ->where('periodic_term', $periodicTerm)
                ->first();

            if ($existingRecord) {
                $existingRecord->update([
                    'quizzez' => $fields['quizzez'] ?? $existingRecord->quizzez,
                    'attendance_behavior' => $fields['attendance_behavior'] ?? $existingRecord->attendance_behavior,
                    'assignments_participations_project' => $fields['assignments_participations_project'] ?? $existingRecord->assignments_participations_project,
                    'exam' => $fields['exam'] ?? $existingRecord->exam,
                    'updated_at' => now(),
                ]);
            } else {
                QuizzesAndScores::create([
                    'classID' => $class,
                    'studentID' => $studentId,
                    'periodic_term' => $periodicTerm,
                    'quizzez' => $fields['quizzez'] ?? null,
                    'attendance_behavior' => $fields['attendance_behavior'] ?? null,
                    'assignments_participations_project' => $fields['assignments_participations_project'] ?? null,
                    'exam' => $fields['exam'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Scores updated successfully.');
    }


}
