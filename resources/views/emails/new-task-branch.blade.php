@extends('emails.base')

@section('header-title', 'Servisso - servicio')

@section('main-title', '¡Hay una tarea de <b>'.$category.'</b> que te podría interesar!')

@section('content')
    <tr>
        <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #95a5a6;background-color:#ecf9fa;padding:20px" st-content="fulltext-paragraph">
            <div style="font-weight:bold">
                {{$userName}}
                <span style="color: #d0cece;font-size:12px">
                    {{$date}}
                </span>
            </div>
            <div>
                {{$description}}
            </div>
        </td>
    </tr>
    <tr>
        <td width="100%" height="10"></td>
    </tr>

@stop

@section('content-button')
    <tr>
        <td>
            <table height="36" align="center" valign="middle" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td style="font-family:Helvetica, arial, sans-serif; text-align:center; font-weight: 300;" width="auto" align="center" valign="middle" height="36">
                            <a target="_blank" href="{{$baseUrl}}/panel/proyectos/{{$taskId}}/{{$taskBranchId}}">
                                Click aqui para ver completo los detalles de la tarea
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
@stop