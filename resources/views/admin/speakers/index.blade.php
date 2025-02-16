@extends('admin.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Ponentes</h2>
        <a href="{{ route('admin.speakers.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Agregar Nuevo
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Foto</th>
                            <th>Especialidad</th>
                            <th>Redes Sociales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ponentes as $ponente)
                        <tr>
                            <td>{{ $ponente['id'] }}</td>
                            <td>{{ $ponente['name'] }}</td>
                            <td>{{ $ponente['photo_url'] }}</td>
                            <td>{{ $ponente['expertise_areas'] ?? 'No especificado' }}</td>
                            <td>{{ $ponente['social_links'] }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.speakers.edit', $ponente['id']) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.speakers.destroy', $ponente['id']) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Estás seguro?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection