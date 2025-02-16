<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use App\Http\Requests\EventRequest;
use App\Http\Requests\SeleccionEventsRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
        $this->middleware(\App\Http\Middleware\CheckApiToken::class)->except(['index', 'show']);
    }

    /**
     * Display a listing of the events.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $events = $this->apiService->get('/events');
        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $ponentes = $this->apiService->get('/ponentes');
        return view('events.create', compact('ponentes'));
    }

    /**
     * Store a newly created event in storage.
     *
     * @param  \App\Http\Requests\EventRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EventRequest $request)
    {
        $response = $this->apiService->post("/events", [
            'titulo' => $request->titulo,
            'tipo' => $request->tipo,
            'ponente_id' => $request->ponente_id,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
        ]);

        if ($response['success']) {
            return redirect()->route('events.index')
                           ->with('success', 'Evento creado exitosamente');
        }

        return back()->with('error', 'No se pudo crear el evento')
                    ->withInput();
    }

    /**
     * Display the specified event.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $event = $this->apiService->get("/events/{$id}");
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $event = $this->apiService->get("/events/{$id}");
        $ponentes = $this->apiService->get('/ponentes');
        return view('events.edit', compact('event', 'ponentes'));
    }

    /**
     * Update the specified event in storage.
     *
     * @param  \App\Http\Requests\EventRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EventRequest $request, $id)
    {
        $response = $this->apiService->put("/events/{$id}", [
            'titulo' => $request->titulo,
            'tipo' => $request->tipo,
            'ponente_id' => $request->ponente_id,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
        ]);

        if ($response['success']) {
            return redirect()->route('events.show', $id)
                           ->with('success', 'Evento actualizado exitosamente');
        }

        return back()->with('error', 'No se pudo actualizar el evento')
                    ->withInput();
    }

    /**
     * Remove the specified event from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = $this->apiService->delete("/events/{$id}");

        if ($response['success']) {
            return redirect()->route('events.index')
                           ->with('success', 'Evento eliminado exitosamente');
        }

        return back()->with('error', 'No se pudo eliminar el evento');
    }

     /**
     * Show registration confirmation page for an event.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm($id)
    {
        $event = $this->apiService->get("/events/{$id}");
        if (empty($event)) {
            return redirect()->route('events.index')
                           ->with('error', 'El evento no se ha encontrado');
        }

        // Check availability
        $availability = $this->apiService->get("/events/{$id}/availability");
        
        if (empty($availability)) {
            return redirect()->route('events.index')
                           ->with('error', 'No se pudo verificar la disponibilidad del evento');
        }

        if ($availability['available_slots'] <= 0) {
            return redirect()->route('events.show', $id)
                           ->with('error', 'No hay plazas disponibles para este evento');
        }

        return view('events.register', [
            'event' => $event,
            'availability' => $availability
        ]);
    }

    /**
     * Automatically register user for an event.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function register($id)
    {
        // Check availability
        $availability = $this->apiService->get("/events/{$id}/availability");
        
        // Verificar si la respuesta es null o está vacía
        if (empty($availability)) {
            return redirect()->route('events.show', $id)
                           ->with('error', 'No se pudo verificar la disponibilidad del evento');
        }

        if ($availability['available_slots'] <= 0) {
            return redirect()->route('events.show', $id)
                           ->with('error', 'No hay plazas disponibles para este evento');
        }

        // Obtener el usuario de la sesión
        $user = Session::get('user');
        if (!$user) {
            return redirect()->route('events.show', $id)
                           ->with('error', 'Debes iniciar sesión para registrarte');
        }

        // Attempt to register
        $response = $this->apiService->post("/events/{$id}/register", [
            'user_id' => $user['id']
        ]);
        // dd($response, $availability);
        // Verificar si la respuesta es null o está vacía
        if (empty($response)) {
            return redirect()->route('events.show', $id)
                           ->with('error', 'No se pudo completar el registro');
        }

        return redirect()->route('events.registration.success', ['id' => $response['event_id']])
                        ->with('success', 'Registro completado con éxito');
    }

    /**
     * Display registration success page.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function registrationSuccess($id)
    {
        $registration = $this->apiService->get("/events/{$id}/register");
        
        if (empty($registration)) {
            return redirect()->route('events.index')
                           ->with('error', 'No se encontró el registro del evento');
        }

        return view('events.registration-success', [
            'registration' => $registration
        ]);
    }

    /**
     * Cancel event registration.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancelRegistration($id)
    {
        $response = $this->apiService->delete("/registrations/{$id}");

        if (empty($response)) {
            return back()->with('error', 'No se pudo cancelar el registro');
        }

        return redirect()->route('events.index')
                        ->with('success', 'Registro cancelado con éxito');
    }
}