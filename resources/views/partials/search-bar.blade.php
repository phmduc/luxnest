{{--
  Reusable search bar component
  Props (pass via @include): $checkIn, $checkOut, $adults, $children, $keyword, $compact
--}}
@php
  $checkIn   = $checkIn   ?? request('check_in','');
  $checkOut  = $checkOut  ?? request('check_out','');
  $adults    = $adults    ?? max(1,(int)request('adults',1));
  $children  = $children  ?? (int)request('children',0);
  $keyword   = $keyword   ?? request('keyword','');
  $compact   = $compact   ?? false;
  $totalGuests = $adults + $children;
  $guestLabel  = $totalGuests > 0 ? $totalGuests.' Khách' : '1 Khách';
  $dateLabel   = ($checkIn && $checkOut)
    ? \Carbon\Carbon::parse($checkIn)->format('d/m').' - '.\Carbon\Carbon::parse($checkOut)->format('d/m/Y')
    : 'Chọn ngày';
@endphp

<div class="lx-search-bar {{ $compact ? 'lx-search-bar--compact' : '' }}" id="lxSearchBar">
  <form action="{{ route('rooms.index') }}" method="GET" class="lx-sb-form" id="lxSbForm">

    {{-- Location --}}
    <div class="lx-sb-field lx-sb-field--dest">
      <i class="ph ph-map-pin lx-sb-icon"></i>
      <div class="lx-sb-body">
        <span class="lx-sb-label">Điểm đến</span>
        <input type="text" name="keyword" class="lx-sb-input" placeholder="Tất cả điểm đến"
               value="{{ $keyword }}" autocomplete="off">
      </div>
    </div>

    <div class="lx-sb-divider"></div>

    {{-- Dates --}}
    <div class="lx-sb-field lx-sb-field--date" id="lxDateField">
      <i class="ph ph-calendar-blank lx-sb-icon"></i>
      <div class="lx-sb-body">
        <span class="lx-sb-label">Thời gian</span>
        <span class="lx-sb-val" id="lxDateDisplay">{{ $dateLabel }}</span>
        <input type="text" id="lxDatePicker" class="lx-sb-date-hidden" placeholder="Chọn ngày" readonly>
        <input type="hidden" name="check_in"  id="lxCheckIn"  value="{{ $checkIn }}">
        <input type="hidden" name="check_out" id="lxCheckOut" value="{{ $checkOut }}">
      </div>
    </div>

    <div class="lx-sb-divider"></div>

    {{-- Guests --}}
    <div class="lx-sb-field lx-sb-field--guests" id="lxGuestField">
      <i class="ph ph-user-plus lx-sb-icon"></i>
      <div class="lx-sb-body">
        <span class="lx-sb-label">Khách</span>
        <span class="lx-sb-val" id="lxGuestDisplay">{{ $guestLabel }}</span>
        <input type="hidden" name="adults"   id="lxAdults"   value="{{ $adults }}">
        <input type="hidden" name="children" id="lxChildren" value="{{ $children }}">
      </div>
      {{-- Guest dropdown --}}
      <div class="lx-sb-guest-drop" id="lxGuestDrop">
        @foreach([['adults','Người lớn','Từ 13 tuổi',$adults],['children','Trẻ em','2–12 tuổi',$children]] as [$type,$label,$sub,$val])
        <div class="lx-sb-guest-row">
          <div>
            <div class="lx-sb-guest-title">{{ $label }}</div>
            <div class="lx-sb-guest-sub">{{ $sub }}</div>
          </div>
          <div class="lx-sb-guest-ctrl">
            <button type="button" class="lx-sb-gc minus" data-type="{{ $type }}">−</button>
            <span class="lx-sb-gc-num" data-type="{{ $type }}">{{ $val }}</span>
            <button type="button" class="lx-sb-gc plus"  data-type="{{ $type }}">+</button>
          </div>
        </div>
        @endforeach
        <button type="button" class="lx-sb-guest-done" onclick="document.getElementById('lxGuestField').classList.remove('open')">
          Xong
        </button>
      </div>
    </div>

    {{-- Submit --}}
    <button type="submit" class="lx-sb-btn" id="lxSbBtn">
      <i class="ph ph-magnifying-glass" id="lxSbIcon"></i>
      <span class="lx-sb-spinner hidden" id="lxSbSpinner">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="animation:lxSpin .7s linear infinite">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31" stroke-dashoffset="10" stroke-linecap="round"/>
        </svg>
      </span>
      @if(!$compact)<span>Tìm phòng</span>@endif
    </button>

  </form>
</div>

<style>
@keyframes lxSpin{to{transform:rotate(360deg)}}
.lx-search-bar{background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.12);border:1px solid #eee;overflow:visible;position:relative;z-index:200;}
.lx-sb-form{display:flex;align-items:stretch;gap:0;min-height:64px;}
.lx-sb-field{display:flex;align-items:center;gap:10px;padding:0 18px;cursor:pointer;flex:1;position:relative;transition:background .15s;}
.lx-sb-field:hover,.lx-sb-field.open{background:#f8fafc;border-radius:12px;}
.lx-sb-field--dest{flex:1.5;}
.lx-sb-field--date{flex:1.5;}
.lx-sb-icon{font-size:1.2rem;color:#1a3a6b;flex-shrink:0;}
.lx-sb-body{display:flex;flex-direction:column;gap:1px;min-width:0;}
.lx-sb-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;}
.lx-sb-input{border:none;outline:none;font-size:.95rem;font-weight:600;color:#0f172a;background:transparent;font-family:inherit;width:100%;padding:0;}
.lx-sb-input::placeholder{color:#94a3b8;font-weight:400;}
.lx-sb-val{font-size:.95rem;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.lx-sb-date-hidden{position:absolute;opacity:0;pointer-events:none;width:0;height:0;}
.lx-sb-divider{width:1px;background:#e2e8f0;margin:14px 0;flex-shrink:0;}
/* Guest dropdown */
.lx-sb-guest-drop{display:none;position:absolute;top:calc(100% + 8px);left:0;right:0;background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.12);border:1px solid #e2e8f0;padding:16px;z-index:999;min-width:280px;}
.lx-sb-field--guests.open .lx-sb-guest-drop{display:block;}
.lx-sb-guest-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f1f5f9;}
.lx-sb-guest-row:last-of-type{border-bottom:none;}
.lx-sb-guest-title{font-size:.9rem;font-weight:600;color:#0f172a;}
.lx-sb-guest-sub{font-size:.75rem;color:#94a3b8;}
.lx-sb-guest-ctrl{display:flex;align-items:center;gap:10px;}
.lx-sb-gc{width:30px;height:30px;border-radius:50%;border:1.5px solid #e2e8f0;background:#fff;font-size:1.1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#1a3a6b;transition:all .2s;}
.lx-sb-gc:hover:not(:disabled){background:#1a3a6b;color:#fff;border-color:#1a3a6b;}
.lx-sb-gc:disabled{opacity:.35;cursor:default;}
.lx-sb-gc-num{width:20px;text-align:center;font-weight:700;font-size:.95rem;}
.lx-sb-guest-done{width:100%;margin-top:12px;padding:10px;background:#1a3a6b;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:.9rem;cursor:pointer;font-family:inherit;}
/* Submit button */
.lx-sb-btn{margin:8px;padding:0 24px;background:#ff5b00;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .2s;white-space:nowrap;font-family:inherit;}
.lx-sb-btn:hover{background:#e04f00;}
.lx-sb-spinner.hidden{display:none;}
/* Compact */
.lx-search-bar--compact .lx-sb-form{min-height:52px;}
.lx-search-bar--compact .lx-sb-field{padding:0 12px;}
.lx-search-bar--compact .lx-sb-btn{padding:0 16px;margin:6px;}
/* Mobile */
@media(max-width:768px){
  .lx-sb-form{flex-wrap:wrap;}
  .lx-sb-field{flex:1 1 100%;padding:10px 14px;border-bottom:1px solid #f1f5f9;}
  .lx-sb-field--date,.lx-sb-field--guests{flex:1 1 calc(50% - 0.5px);}
  .lx-sb-divider{display:none;}
  .lx-sb-btn{flex:1 1 100%;margin:8px;border-radius:10px;padding:13px;justify-content:center;}
}
</style>

<script>
(function(){
  function initLxSearchBar(){
    const form = document.getElementById('lxSbForm');
    if(!form || form._lxInit) return;
    form._lxInit = true;

    // ── Date picker (Litepicker) ──────────────────
    const dateField   = document.getElementById('lxDateField');
    const datePicker  = document.getElementById('lxDatePicker');
    const dateDisplay = document.getElementById('lxDateDisplay');
    const ciInput     = document.getElementById('lxCheckIn');
    const coInput     = document.getElementById('lxCheckOut');

    function fmtDate(d){
      if(!d) return '';
      const dt = typeof d === 'string' ? new Date(d) : d;
      return dt.getDate().toString().padStart(2,'0')+'/'+
             (dt.getMonth()+1).toString().padStart(2,'0')+'/'+dt.getFullYear();
    }

    if(typeof Litepicker !== 'undefined' && datePicker){
      const picker = new Litepicker({
        element: datePicker,
        singleMode: false,
        format: 'YYYY-MM-DD',
        numberOfMonths: window.innerWidth > 768 ? 2 : 1,
        numberOfColumns: window.innerWidth > 768 ? 2 : 1,
        minDate: new Date(),
        autoApply: true,
        setup: p => {
          p.on('selected', (d1, d2) => {
            ciInput.value = d1.format('YYYY-MM-DD');
            coInput.value = d2.format('YYYY-MM-DD');
            dateDisplay.textContent = fmtDate(d1.format('YYYY-MM-DD'))+' - '+fmtDate(d2.format('YYYY-MM-DD'));
          });
        }
      });
      dateField.addEventListener('click', e => { if(!e.target.closest('.lx-sb-guest-drop')) picker.show(); });
    }

    // ── Guest counter ─────────────────────────────
    const guestField   = document.getElementById('lxGuestField');
    const guestDisplay = document.getElementById('lxGuestDisplay');
    const adultsInput  = document.getElementById('lxAdults');
    const childrenInput= document.getElementById('lxChildren');
    let counts = {
      adults:   parseInt(adultsInput.value)||1,
      children: parseInt(childrenInput.value)||0,
    };

    function updateGuests(){
      const total = counts.adults + counts.children;
      guestDisplay.textContent = total + ' Khách';
      adultsInput.value   = counts.adults;
      childrenInput.value = counts.children;
      document.querySelectorAll('.lx-sb-gc.minus').forEach(btn => {
        const t = btn.dataset.type;
        btn.disabled = counts[t] <= (t==='adults'?1:0);
      });
      document.querySelectorAll('.lx-sb-gc-num').forEach(el => {
        el.textContent = counts[el.dataset.type];
      });
    }

    guestField.addEventListener('click', e => {
      if(e.target.closest('.lx-sb-guest-drop') || e.target.closest('.lx-sb-btn')) return;
      e.stopPropagation();
      guestField.classList.toggle('open');
    });

    document.querySelectorAll('.lx-sb-gc').forEach(btn => {
      btn.addEventListener('click', e => {
        e.stopPropagation();
        const t = btn.dataset.type, min = t==='adults'?1:0;
        if(btn.classList.contains('plus')) counts[t]++;
        else if(counts[t]>min) counts[t]--;
        updateGuests();
      });
    });

    document.addEventListener('click', e => {
      if(!e.target.closest('#lxGuestField')) guestField.classList.remove('open');
    });

    updateGuests();

    // ── Submit spinner ────────────────────────────
    form.addEventListener('submit', () => {
      document.getElementById('lxSbIcon').classList.add('hidden');
      document.getElementById('lxSbSpinner').classList.remove('hidden');
    });
  }

  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', initLxSearchBar);
  else initLxSearchBar();
})();
</script>
