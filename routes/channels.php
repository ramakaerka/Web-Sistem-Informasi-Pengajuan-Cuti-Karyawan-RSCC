<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.proses_cuti', function ($user) {
    return $user->role === 'admin' || $user->role === 'manager';
});

Broadcast::channel('karyawan.proses_cuti.{karyawanId}', function ($user, $karyawanId) {
        return (int) $user->id === (int) $karyawanId;
});
Broadcast::channel('manager.proses_cuti.{managerId}', function ($user, $managerId) {
        return (int) $user->id === (int) $managerId;
});
Broadcast::channel('admin.proses_cuti.{karyawanId}', function ($user, $adminId) {
        return (int) $user->id === (int) $adminId;
});
