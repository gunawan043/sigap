<style>
    .bottom-nav {
      position: fixed;
      bottom: 2%;
      left: 5%;
      right: 5%;
      background: #fff;
      border-radius: 2rem;
      box-shadow: 0 0.4rem 1rem rgba(55, 55, 55, 0.2);
      display: flex;
      justify-content: space-around;
      align-items: center;
      z-index: 9999;
    }
  
    .bottom-nav .nav-item {
      color: #b5c4da;
      text-decoration: none;
      font-size: 2.4rem;
      transition: 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 3.5rem;
      height: 3.5rem;
      border-radius: 50%;
    }
  
    .bottom-nav .nav-item.active,
    .bottom-nav .nav-item:hover {
      background: linear-gradient(45deg, #429a90, #2c6c66);
      color: #fff;
    }

    .bottom-nav .nav-item.active img, 
    .bottom-nav .nav-item:hover img { 
      filter: brightness(0) saturate(100%) invert(100%) 
    }

    .bottom-nav .nav-item img{ 
      filter: brightness(100%);
    }
  </style>
  
<div class="bottom-nav">
  @php
      $userId = Auth::user()->id;
  @endphp

  <a href="{{ route('riwayatnya',  $userId) }}" 
    class="nav-item {{ Request::is('riwayat-absensi*') ? 'active' : '' }}">
    <i class="ri-history-fill"></i>
  </a>

  <a href="{{ route('absendong', $userId) }}"
    class="nav-item {{ Request::is('mari-absensi-hari-ini*') ? 'active' : '' }}">
    <img style="" src="{{ URL::asset('build/images/sditlogo.svg') }}" alt="" height="38">
  </a>

  <a href="{{ route('gajikuh', ['id' => $userId]) }}" 
    class="nav-item {{ Request::is('mau-cek-gaji*') ? 'active' : '' }}">
    <i class="ri-currency-fill"></i>
  </a>

</div>

  </div>