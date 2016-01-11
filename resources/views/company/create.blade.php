<h2>Registro Partner</h2>
<div>
	<ul>
	@foreach($errors->all() as $error)
		<li>{!! $error !!}</li>
	@endforeach
	</ul>
</div>
{!! Form::open(['route' => 'company.store','method'=> 'POST' ]) !!}
	Name:		<input name="name" type="text"></br>
	Description:	<input name="description" type="text"></br>
	Tipo:		<select name="category" >
					<option value="1">Mecanico</option>
					<option value="2">Electricista</option>
				</select></br>
				<input type="hidden" value="{!! $partner->id !!}" name="key_p">
				<input name="submit" type="submit" value="Send">
{!! Form::close() !!}