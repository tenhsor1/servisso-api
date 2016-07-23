@extends('emails.base')

@section('header-title', 'Servisso - FAQ')

@section('main-title', '¡Una nueva pregunta o comentario ha sido realizada!')

@section('content')
	<tr>
		<td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #95a5a6;background-color:#ecf9fa;padding:20px" st-content="fulltext-paragraph">                                            
			<div style="font-weight:bold">
				Solicitante: 
				<span style="color: #d0cece;font-size:12px">
				{{$user_name}} 
				</span>&nbsp;&nbsp;&nbsp;
				Enviado: 
				<span style="color: #d0cece;font-size:12px;">
				{{$created_date}}
				</span>
				
			</div>
			<div style="font-weight:bold">
				Comentario/Pregunta: 
				<p style="color: #d0cece;font-size:12px">
				{{$comment}}
				</p>
			</div>
		</td>
	</tr>
	<tr>
		<td width="100%" height="10"></td>
	</tr>

@stop
