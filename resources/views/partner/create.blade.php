<h2>Registro Partner</h2>
<div>
	<ul>
	@foreach($errors->all() as $error)
		<li>{!! $error !!}</li>
	@endforeach
	</ul>
</div>
{!! Form::open(['route' => 'partner.store','method'=> 'POST' ]) !!}
	Email:		<input name="email" type="email"></br>
	Password:	<input name="password" type="password"></br>
	Name:		<input name="name" type="text"></br>
	LastName:	<input name="lastname" type="text"></br>
	Birthdate:	<input name="bithdate" type="text"></br>
	Phone:		<input name="phone" type="text"></br>
	Address:	<input name="address" type="text"></br>
	Zipcode:	<input name="zipcode" type="text"></br>
				<input name="plan" type="hidden" value="1">
	State:		<select name="state" >
					<option value="1">Guadalajara</option>
					<option value="2">Arizona</option>
				</select></br>
	Country:	<select name="country" >
					<option value="1">MX</option>
					<option value="2">EU</option>
				</select></br>
	Status:		<select name="status" >
					<option value="1">Disponible</option>
					<option value="2">No Disponible</option>
				</select></br>
				<input name="submit" type="submit" value="Send">
{!! Form::close() !!}