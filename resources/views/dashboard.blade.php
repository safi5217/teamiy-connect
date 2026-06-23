<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &middot; Teamiy Connect</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <main class="main">
        <div class="wrap-xs">
            <section class="hero">
                <div class="blob"></div>
                <div class="z">
                    <div class="date">{{ now()->format('l, F j') }}</div>
                    <div class="greet">Welcome, {{ auth()->user()->username }}</div>
                    <div class="summary">You are signed in to the employee portal.</div>
                </div>
                <form class="hero-actions" action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="hero-btn" type="submit">Sign out</button>
                </form>
            </section>
        </div>
    </main>
</body>

</html>
