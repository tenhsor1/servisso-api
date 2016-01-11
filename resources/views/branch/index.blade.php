<style>
	form{
		display:inline;
	}
</style>
<h2>BRANCH LIST</h2>
<ul style="list-style-type: none">
	@foreach($branches as $branch)
		<li>
			{!! $branch->address !!}
			{!! Form::open(['route' => ['branch.destroy',$branch->id],'method' => 'DELETE']) !!}
				<input type="submit" name="submit" value="Eliminar">
			{!! Form::close() !!}
		<li>
	@endforeach
</ul>