<?php

namespace App\Http\Controllers;

use App\Mail\CutiDiajukanEmail; 
use App\Mail\CutiDisetujuiEmail; 
use Illuminate\Http\Request;
use App\Models\Cuti;
use App\Mail\OrderShippedMail;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function Ajukan(Request $request,$cutiID)
    {
        $cuti = Cuti::findOrFail($cutiID);
        if($cuti->karyawan){
            // Kirim email ke user
            Mail::to($cuti->karyawan->email)->send(new CutiDiajukanEmail($cuti));
            
            return redirect()->route('karyawan.status')->with('pengajuan success', 'Email notifikasi telah dikirim!');
        }
        elseif($cuti->manager){
            // Kirim email ke user
            Mail::to($cuti->manager->email)->send(new CutiDiajukanEmail($cuti));
            
            return redirect()->route('manager.status')->with('pengajuan success', 'Email notifikasi telah dikirim!');
        }
        elseif($cuti->admin){
            // Kirim email ke user
            Mail::to($cuti->admin->email)->send(new CutiDiajukanEmail($cuti));
            
            return redirect()->route('admin.status')->with('pengajuan success', 'Email notifikasi telah dikirim!');
        }
    }
    public function Disetujui(Request $request,$cutiID)
    {
        $cuti = Cuti::findOrFail($cutiID);
        
        // Kirim email ke user
        if($cuti->karyawan){
            Mail::to($cuti->karyawan->email)->send(new CutiDisetujuiEmail($cuti));
            return redirect()->route('admin.persetujuan')->with('approve success', 'Email Persetujuan Cuti telah dikirim!');
        } elseif ($cuti->manager){
            Mail::to($cuti->manager->email)->send(new CutiDisetujuiEmail($cuti));
            return redirect()->route('admin.persetujuan')->with('approve success', 'Email Persetujuan Cuti telah dikirim!');
        } elseif ($cuti->admin){
            Mail::to($cuti->admin->email)->send(new CutiDisetujuiEmail($cuti));
            return redirect()->route('manager.persetujuan')->with('approve success', 'Email Persetujuan Cuti telah dikirim!');
        }
        
    }
}
