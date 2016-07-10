@extends('emails.base')

@section('header-title', 'Servisso - servicio')

@section('main-title', '¡<b>'.$branchName.'</b> Está interesado en realizar tu proyecto!')

@section('content')
    <tr>
        <td style="font-family: Helvetica, arial, sans-serif; font-size: 16px;line-height: 20px;" st-title="fulltext-title">
            <div style="color: #95a5a6; font-weight: bold; margin-bottom:3px;">
            Has recibido una nueva cotización para tu proyecto:
            <div/>
            <div style="color: #494949; font-size: 14px; background-color: #F6F6F6; padding: 20px;">
                <span style="color: #d0cece;font-size:12px;">
                    {{$taskDate}}
                </span>
                <br/>
                {{$taskDescription}}
            </div>

        </td>
    </tr>
    <tr>
        <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #95a5a6;" st-content="fulltext-paragraph">
            <div style="font-size: 16px; font-weight:bold; margin: 10px 0 5px 0;">Cotización:</div>
        </td>
    </tr>
    <tr>
        <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #494949;background-color:#CCFF66;padding:20px" st-content="fulltext-paragraph">
            <div>
                <b>Precio:</b> ${{$quotePrice}}
            </div>
            <div style="margin-top: 20px; margin-bottom: 20px;">
                <b>Comentarios:</b>
                <br/>
                {{$quoteDescription}}
            </div>
            <div>
                <b>Hecha por:</b> {{$branchName}}
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
                        <td style="font-family:Helvetica, arial, sans-serif; text-align:center; font-weight: 600;" width="auto" align="center" valign="middle" height="36">
                            <a target="_blank" href="{{$baseUrl}}/panel/mis-proyectos/{{$taskId}}/{{$taskBranchId}}">
                                Click aqui para ver completo los detalles completos de la cotización y el proveedor de servicios
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
@stop