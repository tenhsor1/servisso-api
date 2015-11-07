<h2>Registro Branch</h2>
<div>
	<ul>
	@foreach($errors->all() as $error)
		<li>{!! $error !!}</li>
	@endforeach
	</ul>
</div>
{!! Form::open(['route' => 'branch.store','method'=> 'POST' ]) !!}
	Address:	<input name="address" type="text"></br>
	Phone:		<input name="phone" type="text"></br>
	Latitude:	<input name="latitude" type="text"></br>
	Longitude:	<input name="longitude" type="text"></br>
	Schedule:	<input name="schedule" type="text"></br>
				@foreach($companies as $company)
					<input name="key_c" type="hidden" value="{!! $company->id!!}">
					<?php break; ?>
				@endforeach				
				<input name="submit" type="submit" value="Send">
{!! Form::close() !!}