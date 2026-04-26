<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body {
            margin: 0;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Background with blur */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('{{ asset('assets/background/login_background.jpg') }}') no-repeat center center;
            background-size: cover;
            /* Fill the screen */

            /* Adjust blur intensity */
            z-index: -1;
            /* Keep behind all content */
        }

        /* Full-screen layout */




        /* Center the login form */
        .login-layout {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Login form styling */
        .login-form {
            background: rgba(255, 255, 255, 0.85);
            /* semi-transparent white */
            padding: 30px 25px;
            width: 300px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            /* glass effect */
        }

        /* Gradient Login Title */
        .login-form h1 span {
            background: linear-gradient(90deg, #ffc107, #ffb300);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Inputs */
        .login-form input {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ffc107;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        /* Checkbox */
        .login-form input[type="checkbox"] {
            width: 10px;
            accent-color: #ffc107;
            /* amber checkbox */
        }

        /* Submit button */
        .login-form button {
            background: linear-gradient(to right, #ffc107, #ffb300);
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }

        .login-form button:hover {
            background: linear-gradient(to right, #ffb300, #ff9800);
        }
    </style>
    <title>Login Page</title>
</head>

<body>


    <div class="login-layout">
        <form id="LoginForm" action="/login/submit" method="post" class="login-form max-w-sm mx-auto">
            @csrf
            <h1 class="mb-4 text-3xl font-bold text-heading md:text-3xl lg:text-4xl"><span
                    class="text-transparent bg-clip-text bg-gradient-to-r to-amber-600 from-amber-400">Login</span>
            </h1>

            <div class="mb-5">
                <label for="name_email" class="block mb-2.5 text-sm font-medium text-heading">User</label>
                <input type="text" id="name_email" name="name_email"
                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow placeholder:text-body"
                    placeholder="Candy" required />

            </div>
            <div class="mb-5">
                <label for="password-alternative" class="block mb-2.5 text-sm font-medium text-heading">
                    password</label>
                <input type="password" id="password-alternative" name="password"
                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow placeholder:text-body"
                    placeholder="••••••••" required />

            </div>
            <div class="flex items-center mb-5">

                <input type="checkbox" id="remember-me" name="remember_me" required />
                &ensp;
                <small>
                    Remember Me</small>

            </div>
            <button type="button" id="loginBtn"
                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5 focus:outline-none">
                Submit
            </button>
        </form>
    </div>

    {{-- Toast  --}}
    <div id="toastMessage"
        class="fixed top-5 right-5 z-50 hidden flex items-center justify-between max-w-sm w-full bg-white rounded-2xl shadow-2xl p-4 animate-scaleUp">

        <div class="flex items-center space-x-3">
            <span id="toastIcon" class="text-green-500 text-xl">✔️</span>
            <p id="toastText" class="text-gray-800 font-medium"></p>
        </div>

        <button onclick="hideToast()" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
    </div>
</body>
<script>
    // GLOBAL TOAST
    let toastTimeout;

    function showToast({
        message,
        type = "success",
        duration = 5000
    }) {
        const toast = document.getElementById("toastMessage");
        const text = document.getElementById("toastText");
        const icon = document.getElementById("toastIcon");

        // Set message
        text.innerText = message;

        // Set icon and color
        switch (type) {
            case "success":
                toast.classList.remove("bg-red-500", "bg-yellow-500");
                icon.innerText = "✔️";
                icon.classList.add("text-green-500");
                break;
            case "error":
                toast.classList.remove("bg-green-500", "bg-yellow-500");
                icon.innerText = "❌";
                icon.classList.add("text-red-500");
                break;
            case "warning":
                toast.classList.remove("bg-green-500", "bg-red-500");
                icon.innerText = "⚠️";
                icon.classList.add("text-yellow-500");
                break;
        }

        toast.classList.remove("hidden");

        // Auto hide after duration
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            hideToast();
        }, duration);
    }
    // GLOBAL HIDE TOAST
    function hideToast() {
        const toast = document.getElementById("toastMessage");
        toast.classList.add("hidden");

        // Optional: reset icon and text
        document.getElementById("toastText").innerText = "";
        document.getElementById("toastIcon").innerText = "✔️";
    }


    document.getElementById("loginBtn").addEventListener("click", async function() {
        const form = document.getElementById("LoginForm"); // your form element
        const formData = new FormData(form);
        const remember_me = formData.get("remember_me") === "on"; // checkbox value
        const name_email = formData.get("name_email").trim();
        const password = formData.get("password").trim();
        if (!name_email) {
            showToast({
                message: "Name or Email Required.",
                type: "error"
            });
            return;
        }
        if (!password) {
            showToast({
                message: "Password Required.",
                type: "error"
            });
            return;
        }
        try {
            const res = await fetch("/login-submit", {
                method: "POST",
                body: JSON.stringify({
                    name_email,
                    password,
                    remember_me
                }),
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                        .value,
                    Accept: "application/json",
                    "Content-Type": "application/json", // 🔥 must have
                }
            });

            const data = await res.json();

            if (data.success) {
                showToast({
                    message: data.message,
                    type: "success"
                });
                setTimeout(() => {
             window.location.href = data.redirect;
                }, 1000);
            } else {
                showToast({
                    message: data.message,
                    type: "error"
                });
            }

        } catch (err) {
            console.error(err);
            showToast({
                message: "Server error, try again ❌",
                type: "error"
            });
        }
    });
    document.addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
        e.preventDefault(); // prevent any default form submit
        const loginBtn = document.getElementById("loginBtn");
        if (loginBtn) {
            loginBtn.click(); // trigger your login JS
        }
    }
});
</script>

</html>
