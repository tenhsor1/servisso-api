<h2>EDIT VIEW</h2>
{!!Form::model($partner,['route' => ['partner.update', $partner->id],'method' => 'PUT'])!!}
	ID: {!! Form::text('id',null,['disabled'])!!} </br>
	Email: {!! Form::text('email')!!} </br>	
	Password: {!! Form::text('password')!!} </br>
	Name: {!! Form::text('name')!!} </br>
	LastName: {!! Form::text('lastname')!!} </br>
	Birthdate: {!! Form::text('birthdate')!!} </br>
	Phone: {!! Form::text('phone')!!} </br>
	Address: {!! Form::text('address')!!} </br>
	Zipcode: {!! Form::text('zipcode')!!} </br>
	State: {!! Form::text('state')!!} </br>
	Country: {!! Form::text('country')!!} </br>
	Status: {!! Form::text('status')!!} </br>
	<input type="hidden" name="plan" value="{!! $partner->plan_id !!}">
	<input type="submit" name="submit" value="Update">
{!! Form::close() !!}