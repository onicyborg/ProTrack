<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::query()->latest()->get();

        return view('admin.client.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'contact'     => ['nullable', 'string', 'max:255'],
            'address'     => ['nullable', 'string', 'max:1000'],
        ]);

        Client::create($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Klien berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'contact'     => ['nullable', 'string', 'max:255'],
            'address'     => ['nullable', 'string', 'max:1000'],
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Klien berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Klien berhasil dihapus.');
    }
}
