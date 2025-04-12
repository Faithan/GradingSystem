<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Grading System')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('system_images/icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&display=swap"
        rel="stylesheet">

</head>

<body>



    <div class="container">




        {{-- nav bar for all the pages --}}
        <div class="nav-bar" id="navBar">


            <div class="main-nav-contents">

                {{-- for header --}}
                <div class="nav-header">
                    <img src="{{ asset('system_images/icon.png') }}" alt="">
                    <label class="gradient-text">CKCM Grading <em>v.1</em></label>
                </div>

                <!-- Profile Section with Logout Dropdown -->
                <div class="nav-profile">

                    <div style="display: flex; gap:10px;">
                        <img src="{{ asset('system_images/user.png') }}" alt="">

                        <div class="profile">
                            <label for="">{{ Auth::user()->name ?? 'Registrar Name' }}</label>
                            <p>ID#: {{ Auth::user()->studentID ?? '000000' }}</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down"></i>

                    <!-- Hidden Logout Dropdown -->
                    <div class="logout-container">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-btn">
                                <i class="fa-solid fa-arrow-right-from-bracket fa-flip-horizontal"></i> Logout</button>
                        </form>
                    </div>
                </div>

                {{-- for navigation --}}
                <div class="nav-links">
                    <label for="">DASHBOARD</label>

                    <a href="{{ route('index') }}" class="{{ Request::is('/') ? 'active' : '' }}">
                        <i class="fa-solid fa-house"></i><span>Home</span>
                    </a>

                    {{-- If the role is "student" --}}
                    @if (Auth::check() && str_contains(Auth::user()->role, 'student'))
                        <a href="{{ route('my_grades') }}" class="{{ Request::is('my_grades') ? 'active' : '' }}">
                            <i class="fa-solid fa-star"></i>
                            <span>My Grades</span>
                        </a>
                    @endif
                    {{-- End student --}}


                    {{-- If the role is "instructor" --}}
                    @if (Auth::check() && str_contains(Auth::user()->role, 'instructor'))
                        <label for="" style="margin-top:10px;">OPERATION</label>
                        <a href="{{ route('instructor.my_class') }}" class="{{ Request::is('my_class') ? 'active' : '' }}">
                            <i class="fa-regular fa-clipboard"></i>
                            <span>My Class</span>
                        </a>
                    @endif
                    {{-- End instructor --}}

                    {{-- If the role is "dean" --}}
                    @if (Auth::check() && str_contains(Auth::user()->role, 'instructor'))
                        <a href="{{ route('instructor.my_class_archive') }}"
                            class="{{ Request::is('my_class_archive') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-packing"></i><span>My Class Archive</span>
                        </a>
                    @endif
                    {{-- End dean --}}

                    {{-- If the role is "dean" OR "registrar" --}}
                    @if (Auth::check() && (str_contains(Auth::user()->role, 'dean') || str_contains(Auth::user()->role, 'registrar')))
                        <a href="{{ route('registrar_classes') }}"
                            class="{{ Request::is('registrar_classes') ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard"></i> <span>All Class</span>
                        </a>
                    @endif
                    {{-- End dean and registrar --}}


                    {{-- If the role is "registrar" OR "dean" --}}
                    @if (Auth::check() && (str_contains(Auth::user()->role, 'registrar') || str_contains(Auth::user()->role, 'dean')))
                        <a href="{{ route('show.grades') }}" class="{{ Request::is('allgrades') ? 'active' : '' }}">
                            <i class="fa-solid fa-box-archive"></i>
                            <span>All Grades</span>
                        </a>

                        <a href="{{ route('user.show') }}" class="{{ Request::is('users') ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i>
                            <span>Users</span>
                        </a>
                    @endif
                    {{-- End registrar or dean --}}

                    {{-- If the role is "admin" --}}
                    @if (Auth::check() && str_contains(Auth::user()->role, 'admin'))
                        <label for="" style="margin-top:10px;">SETTINGS</label>
                        <a href="">
                            <i class="fa-solid fa-key"></i>
                            <span>Admin</span>
                        </a>
                    @endif
                    {{-- End admin --}}

                </div>
                {{-- End of navigation --}}




            </div>



            {{-- footer --}}
            <div class="main-nav-footer">
                <h4 class="footer-title">POWERED BY CKCM TECH</h4>
                <p>&copy; {{ date('Y') }} CKCM Technologies, LLC</p>
                <p>All Rights Reserved</p>
            </div>
        </div>




        {{-- the main content --}}
        <div class="main-content">

            {{-- for fullscreen and toggle navbar --}}
            <div class="content-header">
                <i class="fa-solid fa-bars" id="menuToggle"></i>
                <div >
                    <i  id="notificationBell" style="margin-right: 20px;" class="fa-solid fa-bell notification-bell" onclick="toggleDropdown()"></i>
                    <i id="fullscreen-icon" class="fa-solid fa-expand" title="Expand"></i>
                </div>

            </div>

            <style>
                .notification-container {
                    position: relative;
                    display: inline-block;
                }

                .notification-bell {
                    cursor: pointer;
                    font-size: 20px;
                }

                .notification-dropdown {
                    display: none;
                    position: absolute;
                    right: 500;
                    background-color: white;
                    min-width: 800px;
                    height: 500px;
                    overflow-y: auto;
                    border: 1px solid #ddd;
                    border-radius: 10px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    z-index: 99;
                }

                .notification-item {
                    padding: 10px;
                    border-bottom: 1px solid #eee;
                }

                .notification-item:last-child {
                    border-bottom: none;
                }

                .notification-dropdown.show {
                    display: block;
                }
            </style>

            @php
                $notifications = collect(); // default empty

                if (Auth::check()) {
                    $studentID = Auth::user()->studentID;

                    $notifications = DB::table('notif_table')
                        ->where(function ($query) use ($studentID) {
                            $query->where('added_by_id', $studentID)
                                ->orWhere('target_by_id', $studentID);
                        })
                        ->orderBy('created_at', 'desc')
                        ->get();
                }

            @endphp

            <div class="notification-container">
                <div id="notificationDropdown" class="notification-dropdown">
                    @if ($notifications->isEmpty())
                        <div class="notification-item">No notifications</div>
                    @else
                        @foreach ($notifications as $notif)
                            <div class="notification-item">
                                <strong>{{ ucfirst($notif->notif_type) }}</strong><br>
                                Department: {{ $notif->department }}<br>
                                Class ID: {{ $notif->class_id }}<br>
                                Class Subject Code: {{ $notif->class_subject_code }} <br>
                                Class Descriptive Title: {{ $notif->class_descriptive_title }} <br>
                                From: {{ $notif->added_by_name }} <br>
                                To: {{ $notif->target_by_name }} <br>
                                {{ $notif->created_at }}

                            </div>
                        @endforeach
                    @endif
                </div>
            </div>


            <script>
                const bell = document.getElementById("notificationBell");

                function toggleDropdown() {
                    document.getElementById("notificationDropdown").classList.toggle("show");
                }

                // Optional: close dropdown if clicked outside
                document.addEventListener("click", function (event) {
                    const dropdown = document.getElementById("notificationDropdown");

                    // Now uses the actual `bell` constant we declared
                    if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
                        dropdown.classList.remove("show");
                    }
                });
            </script>





            <script>
                $(document).ready(function () {
                    // Handle navbar state from localStorage
                    if (localStorage.getItem('navMinimized') === 'true') {
                        $('#navBar').addClass('minimized');
                    }

                    $('#menuToggle').click(function () {
                        $('#navBar').toggleClass('minimized');
                        localStorage.setItem('navMinimized', $('#navBar').hasClass('minimized'));
                    });

                    // Handle fullscreen mode with localStorage
                    const icon = document.getElementById("fullscreen-icon");

                    if (localStorage.getItem('fullscreen') === 'true') {
                        document.documentElement.requestFullscreen();
                        icon.classList.replace("fa-expand", "fa-compress");
                        icon.setAttribute("title", "Compress");
                    }

                    icon.addEventListener("click", () => {
                        if (!document.fullscreenElement) {
                            document.documentElement.requestFullscreen();
                            icon.classList.replace("fa-expand", "fa-compress");
                            icon.setAttribute("title", "Compress");
                            localStorage.setItem('fullscreen', 'true');
                        } else {
                            document.exitFullscreen();
                            icon.classList.replace("fa-compress", "fa-expand");
                            icon.setAttribute("title", "Expand");
                            localStorage.setItem('fullscreen', 'false');
                        }
                    });
                });
            </script>


            {{-- for dynamic contect base on the page, must not edit or touch --}}
            <div class="content">
                @yield('content')
            </div>
        </div>






    </div>














    <!-- for logout -->

    <script>
        $(document).ready(function () {
            $(".nav-profile").click(function (event) {
                event.stopPropagation();
                $(".logout-container").toggleClass("show");
            });

            $(document).click(function (event) {
                if (!$(event.target).closest(".nav-profile").length) {
                    $(".logout-container").removeClass("show");
                }
            });
        });
    </script>














    <style>
        .container {
            width: 100%;
            height: 100%;
            background-color: var(--ckcm-color1);
            display: flex;
            flex-direction: row;
        }

        .nav-bar {
            width: 280px;
            height: 100%;
            background-color: var(--ckcm-color2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid var(--color7);
            overflow-y: auto;
            /* Enables scrolling when content overflows */
            overflow-x: hidden;
            /* Hides horizontal scroll */
            transition: width 0.3s ease;

        }

        .main-nav-footer {
            text-align: left;
            padding: 10px;
            color: var(--color1);
            line-height: 1.2;
            border-top: var(--color6) 1px solid;
        }

        .main-nav-footer h4 {
            margin-bottom: 2px;
            font-size: 1.1rem;
            color: var(--color3);
        }

        .main-nav-footer p {
            color: var(--color5);
        }

        .main-nav-contents {
            display: flex;
            flex-direction: column;

        }

        .nav-header {
            display: flex;
            flex-direction: row;
            padding: 20px;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .nav-header img {
            width: 30px;

        }

        .nav-profile {
            cursor: pointer;
            position: relative;
            gap: 10px;
            margin: 0 10px;
            transition: background 0.3s ease-in-out;
            display: flex;
            flex-direction: row;
            padding: 10px;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            background-color: var(--ckcm-color2);
            border-left: 0;
            border-right: 0;
            border-radius: 5px;
            /* border: 1px solid var(--color6); */
        }

        .nav-profile:hover {
            background-color: var(--hover-background-color);
        }

        .nav-profile img {
            width: 30px;
            max-height: 30px;
        }

        .nav-profile i {
            color: var(--color1);
            font-size: 1.2rem;
        }

        .profile label {
            color: var(--color1);
            font-size: 1.2rem;
            font-weight: bold;
            text-transform: capitalize;
        }

        .profile p {
            color: var(--color6);
            font-size: 1.2rem;

        }

        .nav-links label {
            color: var(--color6);
            font-size: 1.1rem;
            font-weight: bold;
            padding: 10px 5px;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            padding: 10px;

        }

        .nav-links a {
            font-size: 1.3rem;
            border-radius: 5px;
            color: var(--color4);
            text-decoration: none;
            padding: 10px;
            margin-bottom: 5px;
        }

        .nav-links a:hover {
            color: var(--color1);
            background-color: var(--hover-background-color);
        }

        .nav-links a.active {
            color: var(--color1);
            background-color: var(--ckcm-color1);
            /* border: 1px solid var(--color6); */
        }

        .nav-links i {
            margin-right: 5px;

        }


        /* main content */

        .main-content {
            display: flex;
            flex-direction: column;
            width: 100%;
            overflow-x: auto
        }

        .content-header {
            display: flex;
            padding: 12px;
            background-color: var(--ckcm-color1);
            border-bottom: 1px solid var(--color8);
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .content-header i {
            font-size: 1.5rem;
            color: var(--color6);
            cursor: pointer;
            transition: transform 0.2s ease, color 0.2s ease;

        }

        .content-header i:hover {
            color: #3498db;
            /* Change color on hover */
        }


        /* dynamic content */
        .content {
            overflow-y: scroll;
            overflow-x: auto;
        }
    </style>




    <style>
        /* Navigation profile styles */
        /* Logout dropdown */
        .logout-container {
            position: absolute;
            top: 110%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--ckcm-color1);
            width: 100%;
            text-align: center;

            border-radius: 5px;
            opacity: 0;
            visibility: hidden;
            transform-origin: top center;
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
            border: 1px solid var(--color7);
        }

        .logout-container.show {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .logout-btn {
            background: var(--ckcm-color1);
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: var(--hover-background-color);
        }
    </style>






    {{-- minimized style --}}
    <style>
        .nav-bar.minimized {
            width: 60px;
            transition: ease-in-out 0.3s;
        }


        .nav-bar.minimized .nav-links a span,
        .nav-bar.minimized .nav-header label,
        .nav-bar.minimized .profile,
        .nav-bar.minimized .nav-profile i,
        .nav-bar.minimized .nav-links label,
        .nav-bar.minimized .main-nav-footer p {
            display: none;
        }



        .nav-bar.minimized .nav-links {
            gap: 10px;
        }

        .nav-bar.minimized .nav-links a {
            text-align: center;
            padding: 10px;
        }

        .nav-bar.minimized .nav-header {
            padding: 20px 10px;
        }

        .nav-bar.minimized .main-nav-footer h4 {
            font-size: .8rem;
        }

        .nav-bar.minimized img {
            width: 25px;
        }

        .nav-bar.minimized .logout-btn {
            font-size: 1rem;
            margin: 0;
            padding: 5px;
        }



        .nav-bar.minimized .nav-profile {
            margin: 0;
            justify-content: center;
        }
    </style>





    {{-- for mobile --}}
    <style>
        @media (max-width: 480px) {


            .content-header i:nth-child(2) {
                display: none;
            }


            .nav-bar.minimized {
                width: 65px;
                display: flex;
            }


            .nav-bar.minimized .nav-links a span,
            .nav-bar.minimized .nav-header label,
            .nav-bar.minimized .profile,
            .nav-bar.minimized .nav-profile i,
            .nav-bar.minimized .nav-links label,
            .nav-bar.minimized .main-nav-footer p {
                display: none;
            }



            .nav-bar.minimized .nav-links {
                gap: 10px;
            }

            .nav-bar.minimized .nav-links a {
                text-align: center;
                padding: 10px;
            }

            .nav-bar.minimized .nav-header {
                padding: 20px 10px;
            }

            .nav-bar.minimized .main-nav-footer h4 {
                font-size: .6rem;
            }

            .nav-bar.minimized img {
                width: 25px;
            }

            .nav-bar.minimized .logout-btn {
                font-size: 1rem;
                margin: 0;
                padding: 5px;
            }

            .nav-bar.minimized .nav-profile {
                margin: 0;
                justify-content: center;
            }
        }
    </style>

    {{-- tablet --}}
    <style>
        @media (max-width: 768px) {


            .nav-bar {

                display: none;
            }

            .content-header i:nth-child(2) {
                display: none;
            }


            .nav-bar.minimized {
                width: 65px;
                display: flex;
            }


            .nav-bar.minimized .nav-links a span,
            .nav-bar.minimized .nav-header label,
            .nav-bar.minimized .profile,
            .nav-bar.minimized .nav-profile i,
            .nav-bar.minimized .nav-links label,
            .nav-bar.minimized .main-nav-footer p {
                display: none;
            }



            .nav-bar.minimized .nav-links {
                gap: 10px;
            }

            .nav-bar.minimized .nav-links a {
                text-align: center;
                padding: 10px;
            }

            .nav-bar.minimized .nav-header {
                padding: 20px 10px;
            }

            .nav-bar.minimized .main-nav-footer h4 {
                font-size: .6rem;
            }

            .nav-bar.minimized img {
                width: 25px;
            }

            .nav-bar.minimized .logout-btn {
                font-size: 1rem;
                margin: 0;
                padding: 5px;
            }

            .nav-bar.minimized .nav-profile {
                margin: 0;
                justify-content: center;
            }
        }
    </style>




    {{-- laptop --}}
    <style>
        @media (max-width: 1024px) {
            .nav-bar {
                display: none;
            }

            .nav-bar.minimized {
                width: 65px;
                display: flex;
            }


            .nav-bar.minimized .nav-links a span,
            .nav-bar.minimized .nav-header label,
            .nav-bar.minimized .profile,
            .nav-bar.minimized .nav-profile i,
            .nav-bar.minimized .nav-links label,
            .nav-bar.minimized .main-nav-footer p {
                display: none;
            }



            .nav-bar.minimized .nav-links {
                gap: 10px;
            }

            .nav-bar.minimized .nav-links a {
                text-align: center;
                padding: 10px;
            }

            .nav-bar.minimized .nav-header {
                padding: 20px 10px;
            }

            .nav-bar.minimized .main-nav-footer h4 {
                font-size: .8rem;
            }

            .nav-bar.minimized img {
                width: 25px;
            }

            .nav-bar.minimized .logout-btn {
                font-size: 1rem;
                margin: 0;
                padding: 5px;
            }



            .nav-bar.minimized .nav-profile {
                margin: 0;
                justify-content: center;
            }
        }
    </style>


</body>

</html>
