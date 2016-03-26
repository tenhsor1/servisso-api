Hola {{$user->name}},
<br>
Presiona aquí para cambiar tu contraseña: <a href="{{ Config::get('app.front_url')."/password/reset/".$token }}">Cambiar mi contraseña</a>