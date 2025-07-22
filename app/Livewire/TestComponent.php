<?php

namespace App\Livewire;

use Livewire\Component;

class TestComponent extends Component
{
    public $jumlahCuti = 20;
    public function render()
    {
        return view('livewire.test-component',[
            'jumlahCuti' => $this->jumlahCuti // Kirim eksplisit
        ]);
    }
}
