<h2>EDIT BRANCH</h2>
{!! Form::model($branch,['route' => ['branch.update', $branch->id],'method' => 'PUT'])!!}
	<b>Branch info:</b></br>
	ID: {!! Form::text('id',null,['disabled'])!!} </br>
	Address: {!! Form::text('address')!!} </br>
	Phone: {!! Form::text('phone')!!} </br>
	Schedule: {!! Form::text('schedule')!!} </br>
	Latitude: {!! Form::text('latitude')!!} </br>
	Longitude: {!! Form::text('longitude')!!} </br>
	<input type="submit" name="submit" value="Update">
{!! Form::close() !!}