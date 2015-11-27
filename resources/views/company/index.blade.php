<style>
form{
	display:inline;
}
</style>
<h2>COMPANIES LIST</h2>

<ul style="list-style-type: none">
	@foreach($companies as $company)
		<li>
			{!! $company->name !!}
			{!! Form::open(['route' => ['company.destroy',$company->id],'method' => 'DELETE']) !!}
				<input type="submit" name="submit" value="Eliminar">
			{!! Form::close() !!}
		</li>
	@endforeach
</ul>