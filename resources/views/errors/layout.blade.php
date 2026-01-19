<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>
    @vite(['resources/css/app.css'])

		<script>
			if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
				document.documentElement.classList.add('dark');
			} else {
				document.documentElement.classList.remove('dark');
			}
		</script>
</head>

<body class="h-full font-sans antialiased bg-background">
	<main class="grid min-h-full place-items-center px-6 py-24 sm:py-32 lg:px-8">
		<div class="text-center">
			<p class="mb-4 text-7xl tracking-tight font-extrabold lg:text-9xl ">@yield('code')</p>
			<h1 class="mb-4 text-3xl tracking-tight font-bold text-gray-900 md:text-4xl dark:text-white">@yield('title')</h1>
			<p class="mb-4 text-lg font-light text-gray-500 dark:text-gray-400">@yield('message')</p>
			<div class="mt-10 flex items-center justify-center gap-x-6">
				<a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2 has-[>svg]:px-3">Go back home</a>
			</div>
		</div>
	</main>
</body>

</html>
