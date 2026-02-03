<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuItem;
use App\Http\Controllers\Admin\LogController; 
use Illuminate\Support\Facades\File; 

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category') && $request->category != 'all') {
            $query->where('category', $request->category);
        }

        $menuItems = $query->orderBy('category')->orderBy('name')->paginate(10);

        return view('admin.menu.index', compact('menuItems'));
    }

    public function store(Request $request)
    {
        // --- UPDATE: Limit dinaikkan ke 20MB (20480 KB) ---
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480', // Support 4K (Max 20MB)
        ], [
            'image.max' => 'Ukuran gambar maksimal 20MB!',
            'image.image' => 'File harus berupa gambar!',
        ]);

        $data = $request->except(['_token', 'image']);

        // Upload Image
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // Tambahkan time() agar nama file unik
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Simpan ke public/uploads/menus
            $file->move(public_path('uploads/menus'), $filename);
            $data['image_url'] = 'uploads/menus/' . $filename;
        } else {
            $data['image_url'] = 'assets/images/default-food.png';
        }

        $data['is_available'] = $request->has('is_available') ? 1 : 0;
        $data['show_on_homepage'] = $request->has('show_on_homepage') ? 1 : 0;
        $data['is_favorite'] = $request->has('is_favorite') ? 1 : 0;

        $menu = MenuItem::create($data);

        LogController::record(auth()->id(), 'Create', 'Menu Management', "Menambahkan menu: {$menu->name}", null, 'info');

        return redirect()->back()->with('success', 'Menu berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $menu = MenuItem::findOrFail($id);
        $oldData = $menu->toArray();

        // --- UPDATE: Limit dinaikkan ke 20MB (20480 KB) ---
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480', // Support 4K (Max 20MB)
        ], [
            'image.max' => 'Ukuran gambar maksimal 20MB!',
            'image.image' => 'File harus berupa gambar!',
        ]);

        $data = $request->except(['_token', '_method', 'image']);

        // Logika Ganti Gambar
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika bukan default
            if ($menu->image_url && file_exists(public_path($menu->image_url))) {
                if(strpos($menu->image_url, 'default') === false) {
                    File::delete(public_path($menu->image_url));
                }
            }

            // Upload gambar baru
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/menus'), $filename);
            $data['image_url'] = 'uploads/menus/' . $filename;
        }

        $data['is_available'] = $request->has('is_available') ? 1 : 0;
        $data['show_on_homepage'] = $request->has('show_on_homepage') ? 1 : 0;
        $data['is_favorite'] = $request->has('is_favorite') ? 1 : 0;

        $menu->update($data);

        LogController::record(auth()->id(), 'Update', 'Menu Management', "Update menu: {$menu->name}", json_encode(['old' => $oldData, 'new' => $menu->fresh()->toArray()]), 'warning');

        return redirect()->back()->with('success', 'Menu berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $menu = MenuItem::findOrFail($id);
        $backup = $menu->toArray();

        if ($menu->image_url && file_exists(public_path($menu->image_url))) {
            if(strpos($menu->image_url, 'default') === false) {
                File::delete(public_path($menu->image_url));
            }
        }

        $menu->delete();

        LogController::record(auth()->id(), 'Delete', 'Menu Management', "Hapus menu: {$backup['name']}", json_encode($backup), 'danger');

        return redirect()->back()->with('success', 'Menu berhasil dihapus!');
    }
}