<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\SignatureController;
use App\Livewire\TestComponent;
use Illuminate\Support\Facades\Route;
use App\Helpers\HariLiburHelper;
use App\Livewire\CutiDashboard;


Route::middleware('web')->group(function(){
    Route::get('/',[LoginController::class,'index']);
    Route::get('/sync-libur', function () {
        HariLiburHelper::ambilDanSimpanHariLibur();
        return 'Data hari libur berhasil disimpan ke database!';
    });
    Route::get('/login',[LoginController::class,'index'])->name('login');
    Route::post('/cekLogin',[LoginController::class,'cekLogin'])->name('cekLogin');
    Route::get('/generate_hash',function(){
        return view('generate_hash');
    });
});
// Lupa Password Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware(['auth','role:admin'])->group(function(){
    Route::get('/admin/profileEdit',[AdminController::class,'profileEdit'])->name('admin.profileEdit');
    Route::post('/admin/profileEdit/save',[AdminController::class,'saveProfile'])->name('admin.profileEdit.save');
    Route::post('/admin/profileEdit/signature/upload',[AdminController::class,'upload'])->name('admin.profileEdit.signature.upload');
    Route::get('/admin/profile',[AdminController::class,'profile'])->name('admin.profile');
    Route::get('/admin/addUnitJabatan',[AdminController::class,'addUnitJabatan'])->name('admin.addUnitJabatan');
    Route::post('/admin/addUnitJabatan/save',[AdminController::class,'saveUnitJabatan'])->name('admin.saveUnitJabatan');
    Route::get('/admin/addProfileKaryawan',[AdminController::class,'addProfile'])->name('admin.addProfileKaryawan');
    Route::post('/admin/addProfileKaryawan/save',[AdminController::class,'saveProfileKaryawan'])->name('admin.saveProfileKaryawan');
    Route::get('/admin/pengajuan',[AdminController::class,'pengajuan'])->name('admin.pengajuan');
    Route::post('/admin/pengajuan/store',[AdminController::class,'store'])->name('admin.pengajuan.store');
    Route::get('/admin/pengajuan/email/{id}',[EmailController::class,'Ajukan'])->name('admin.email');
    Route::get('/admin/persetujuan',[AdminController::class,'persetujuan'])->name('admin.persetujuan');
    Route::get('/admin/persetujuan/delete/{id}',[AdminController::class,'destroy'])->name('admin.persetujuan.delete');
    Route::post('/admin/persetujuan/{id}/proses',[AdminController::class,'prosesCuti'])->name('admin.persetujuan.prosesCuti');
    Route::get('/admin/persetujuan/email/karyawan/{id}',[EmailController::class,'Disetujui'])->name('admin.email.to.karyawan');
    Route::get('/admin/persetujuan/email/manager/{id}',[EmailController::class,'Disetujui'])->name('admin.email.to.manager');
    Route::get('/admin/statusCuti',[AdminController::class,'status'])->name('admin.status');
    Route::get('/admin/statusCuti/view_pdf/{id}',[AdminController::class,'view_pdf'])->name('admin.status.view_pdf');
    Route::get('/admin/statusCuti/download_pdf/{id}',[AdminController::class,'download_pdf'])->name('admin.status.download_pdf');
    Route::get('/admin/laporan',CutiDashboard::class)->name('admin.laporan');
    Route::get('/admin/laporan/download_suket/{id}',[AdminController::class,'downloadSuket'])->name('admin.downloadSuket');
    Route::get('/admin/tes',[TestComponent::class])->name('admin.tes');
    Route::get('/logout',[LoginController::class,'logout'])->name('admin.logout');
    
});


Route::middleware(['auth','role:manager'])->group(function(){
    Route::get('/manager/profileEdit',[ManagerController::class,'profileEdit'])->name('manager.profileEdit');
    // Route::get('/manager/profileEdit/signature',[SignatureController::class,'index'])->name('manager.profileEdit.signature');
    Route::post('/manager/profileEdit/signature/upload',[ManagerController::class,'upload'])->name('manager.profileEdit.signature.upload');
    Route::post('/manager/profileEdit/save',[ManagerController::class,'saveProfile'])->name('manager.profileEdit.save');
    Route::get('/manager/profile',[ManagerController::class,'profile'])->name('manager.profile');
    Route::get('/manager/pengajuan',[ManagerController::class,'pengajuan'])->name('manager.pengajuan');
    Route::post('/manager/pengajuan/store',[ManagerController::class,'store'])->name('manager.pengajuan.store');
    Route::get('/manager/pengajuan/email/{id}',[EmailController::class,'Ajukan'])->name('manager.email');
    Route::get('/manager/persetujuan',[ManagerController::class,'persetujuan'])->name('manager.persetujuan');
    Route::get('/manager/persetujuan/delete/{id}',[ManagerController::class,'destroy'])->name('manager.persetujuan.delete');
    Route::post('/manager/persetujuan/update',[ManagerController::class,'updateCutiStatus'])->name('manager.persetujuan.update.no.atasan');
    Route::post('/manager/persetujuan/{id}/proses',[ManagerController::class,'prosesCuti'])->name('manager.persetujuan.prosesCuti');
    Route::get('/manager/persetujuan/email/admin/{id}',[EmailController::class,'Disetujui'])->name('manager.email.to.admin');
    Route::get('/manager/persetujuan/download_suket/{id}',[ManagerController::class,'downloadSuket'])->name('manager.downloadSuket');
    Route::get('/manager/statusCuti',[ManagerController::class,'status'])->name('manager.status');
    Route::get('/manager/statusCuti/download_pdf/{id}',[ManagerController::class,'download_pdf'])->name('manager.status.download_pdf');
    Route::get('/manager/printCuti/{id}',[ManagerController::class,'detailCuti'])->name('manager.printCuti');
    Route::get('/logout',[LoginController::class,'logout'])->name('manager.logout');
});

Route::middleware(['auth','role:karyawan'])->group(function(){
    Route::get('/karyawan/profileEdit',[KaryawanController::class,'profileEdit'])->name('karyawan.profileEdit');
    Route::post('/karyawan/profileEdit/save',[KaryawanController::class,'saveProfile'])->name('karyawan.profileEdit.save');
    Route::post('/karyawan/profileEdit/signature/upload',[KaryawanController::class,'upload'])->name('karyawan.profileEdit.signature.upload');
    Route::get('/karyawan/profile',[KaryawanController::class,'profile'])->name('karyawan.profile');
    Route::get('/karyawan/pengajuan',[KaryawanController::class,'pengajuan'])->name('karyawan.pengajuan');
    Route::post('/karyawan/pengajuan/store',[KaryawanController::class,'store'])->name('karyawan.pengajuan.store');
    Route::get('/karyawan/pengajuan/email/{id}',[EmailController::class,'Ajukan'])->name('karyawan.email');
    Route::get('/karyawan/persetujuan/delete/{id}',[KaryawanController::class,'destroy'])->name('karyawan.persetujuan.delete');
    Route::get('/karyawan/statusCuti',[KaryawanController::class,'status'])->name('karyawan.status');
    Route::get('/karyawan/statusCuti/download_pdf/{id}',[KaryawanController::class,'download_pdf'])->name('karyawan.status.download_pdf');
    Route::get('/logout',[LoginController::class,'logout'])->name('karyawan.logout');
});
