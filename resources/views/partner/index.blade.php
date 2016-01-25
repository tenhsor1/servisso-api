<style>
form{
	display:inline;
}
</style>
<h2>LISTA PARTNERS</h2>
<ul style="list-style-type: none">
	@foreach($partners as $partner)
		<li style="padding:10px">
			{!! $partner->name !!}
			{!! Form::open(['route' => ['partner.destroy',$partner->id],'method' => 'DELETE']) !!}
				<input type="submit" value="Eliminar">
			{!! Form::close() !!}
			
			</br><b>Companies:</b><ul>
				@foreach($partner->companies as $company)
				<li>
					{!! $company->name !!}
					</br><b>Branches:</b><ul>
						@foreach($company->branches as $branch)
							<li>
								{!! $branch->address !!}
							</li>
						@endforeach
					</ul>
				</li>
				@endforeach
			</ul>
		</li>
	@endforeach
</ul>