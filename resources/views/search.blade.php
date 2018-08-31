<html lang="en">
<head>
	<title><?php echo $title;?></title>
</head>
<body>
	<h1>Welcome to {{$title}}</h1>
	<h2>This is a data scrapper for target.com</h2>
	<form action="{{url('/search/result')}}" method="post">
		{{csrf_field()}}
		<input type="text" name="search"><button type="submit">Search</button>
	</form>
	@if ($errors->any())
		@foreach ($errors->all() as $error)
			{{ $error }}
		@endforeach
	@endif
</body>
</html>
