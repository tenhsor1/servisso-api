<h2>EDIT VIEW</h2>
{!!Form::model($company,['route' => ['company.update', $company->id],'method' => 'PUT'])!!}
	ID: {!! Form::text('id',null,['disabled'])!!} </br>
	Name: {!! Form::text('name')!!} </br>
	Description: {!! Form::text('description')!!} </br>
	Category: {!! Form::text('category_id')!!} </br>
	<input type="submit" name="submit" value="Update">
{!! Form::close() !!}