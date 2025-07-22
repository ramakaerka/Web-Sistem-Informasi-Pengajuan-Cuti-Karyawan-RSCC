<aside id="sidebar">
    <div class="d-flex">
        <button class="toggle-btn mt-3" type="button">
            <i class="fa-solid fa-cubes"></i>
        </button>
        <div class="sidebar-logo mt-3">
            <a href="/main">Sistem Pengajuan Cuti RSCC</a>
        </div>
    </div>
    <ul class="sidebar-nav">
        @switch(Auth::user()->role)
            @case('admin')
            <li class="sidebar-item">
                <a href="/admin/laporan" class="sidebar-link">
                    <i class="fa-regular fa-folder-open"></i>
                    <span>Dashboard Cuti</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/admin/persetujuan" class="sidebar-link">
                    <i class="fa-duotone fa-solid fa-clipboard-check"></i>
                    <span>Persetujuan Cuti</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/admin/profile" class="sidebar-link">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Profile Saya</span>
                </a>
            </li>
            {{-- <li class="sidebar-item">
                <a href="/admin/addUnitJabatan" class="sidebar-link">
                    <i class="fa-solid fa-landmark"></i>
                    <span>Add Unit & Jabatan</span>
                </a>
            </li> --}}
            <li class="sidebar-item">
                <a href="/admin/addProfileKaryawan" class="sidebar-link">
                    <i class="fa-solid fa-users"></i>
                    <span>Add User Karyawan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/admin/pengajuan" class="sidebar-link">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Pengajuan Cuti</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="/admin/statusCuti" class="sidebar-link">
                    <i class="fa-solid fa-tower-broadcast"></i>
                    <span>Status Cuti Saya</span>
                </a>
            </li>
            
                @break
            @case('manager')
            <li class="sidebar-item">
                <a href="/manager/profile" class="sidebar-link">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Profile Saya</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/manager/pengajuan" class="sidebar-link">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Pengajuan Cuti</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/manager/persetujuan" class="sidebar-link">
                    <i class="fa-duotone fa-solid fa-clipboard-check"></i>
                    <span>Persetujuan Cuti</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/manager/statusCuti" class="sidebar-link">
                    <i class="fa-solid fa-tower-broadcast"></i>
                    <span>Status Cuti</span>
                </a>
            </li>
                @break
            @case('karyawan')
            <li class="sidebar-item">
                <a href="/karyawan/profile" class="sidebar-link">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Profile Saya</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/karyawan/pengajuan" class="sidebar-link">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Pengajuan Cuti</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="/karyawan/statusCuti" class="sidebar-link">
                    <i class="fa-solid fa-tower-broadcast"></i>
                    <span>Status Cuti</span>
                </a>
            </li>
                @break
        @endswitch
    </ul>
        {{-- <li class="sidebar-item">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                <i class="lni lni-protection"></i>
                <span>Auth</span>
            </a>
            <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">Login</a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">Register</a>
                </li>
            </ul>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                <i class="lni lni-layout"></i>
                <span>Multi Level</span>
            </a>
            <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed" data-bs-toggle="collapse"
                        data-bs-target="#multi-two" aria-expanded="false" aria-controls="multi-two">
                        Two Links
                    </a>
                    <ul id="multi-two" class="sidebar-dropdown list-unstyled collapse">
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Link 1</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Link 2</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="lni lni-popup"></i>
                <span>Notification</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="lni lni-cog"></i>
                <span>Setting</span>
            </a>
        </li>
    </ul> --}}
    <div class="sidebar-footer">
        <a href="/logout" class="sidebar-link">
            <i class="fa-solid fa-left-long"></i>            
            <span>Logout</span>
        </a>
    </div>

</aside>
<script src='{{ asset('sidebar.js') }}'></script>


