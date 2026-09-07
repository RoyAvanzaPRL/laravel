<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda</title>
</head>
<body>
    <h1>Contactos</h1>

    <p><a href="{{ route('contacts.create') }}">Nuevo contacto</a></p>

    <ul>
        @forelse ($contacts as $contact)
            <li>
                {{ $contact->name }}
                {{ $contact->email }}
                {{ $contact->phone }}
                {{ $contact->note }}
                <a href="{{ route('contacts.show', $contact) }}">Ver</a>
                <a href="{{ route('contacts.edit', $contact) }}">Editar</a>
                <form action="{{ route('contacts.destroy', $contact) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Eliminar</button>
                </form>
            </li>
        @empty
            <li>No hay contactos todavía.</li>
        @endforelse
    </ul>
</body>
</html>