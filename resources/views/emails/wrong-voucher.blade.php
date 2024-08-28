<!DOCTYPE html>
<html>

<head>
    <title>Comprobantes Incorrectos</title>
</head>

<body>
    <h1>Estimado {{ $user->name }},</h1>
    <p>Hemos encontrado tus comprobantes con los siguientes detalles:</p>
    @foreach ($comprobantes as $comprobante)
        <ul>
            <li>Error: {{ $comprobante['reason'] }}</li>
            @if ($comprobante['file_was_created'])
                <li>Archivo: {{ $comprobante['filename'] }}</li>
            @else
                <li>No se puedo crear el archivo el formato no es correcto.</li>
            @endif
        </ul>
    @endforeach
    <p>¡Gracias por usar nuestro servicio!</p>
</body>

</html>
