<div
  class="grid gap-2"
  style="
    grid-template-columns: repeat(13, 58px);
    grid-template-rows: repeat(24, 8px);
  "
>
  <a
    href="{{ \App\Filament\Resources\EventResource::getUrl('create', [
      'room_id' => 3
    ]) }}"
    class="flex items-center justify-center"
    style="grid-column: 2 / 4; grid-row: 2 / 5; background-color: #D9EBD3;"
  >
    <span class="font-bold text-xs text-center">會議室 3 (中型)</span>
  </a>
  <a
    href="{{ \App\Filament\Resources\EventResource::getUrl('create', [
      'room_id' => 2
    ]) }}"
    class="flex items-center justify-center"
    style="grid-column: 4 / 6; grid-row: 2 / 5; background-color: #D9EBD3;"
  >
    <span class="font-bold text-xs text-center">會議室 2 (中型)</span>
  </a>
  <div class="flex items-center justify-center" style="grid-column: 8 / 9; grid-row: 1 / 5; background-color: lightgray;">
    <x-filament::link tooltip="🐈">
      <span class="font-bold text-xs text-center">豆芽房</span>
    </x-filament::link>
  </div>
  <a
    href="{{ \App\Filament\Resources\EventResource::getUrl('create', [
      'room_id' => 4
    ]) }}"
    class="flex items-center justify-center"
    style="grid-column: 1 / 2; grid-row: 7 / 10; background-color: #D9EBD3;"
  >
    <span class="font-bold text-xs text-center">會議室 4 (小型)</span>
  </a>
  <a
    href="{{ \App\Filament\Resources\EventResource::getUrl('create', [
      'room_id' => 5
    ]) }}"
    class="flex items-center justify-center"
    style="grid-column: 1 / 2; grid-row: 10 / 13; background-color: #D9EBD3;"
  >
    <span class="font-bold text-xs text-center">會議室 5 (小型)</span>
  </a>
  <a
    href="{{ \App\Filament\Resources\EventResource::getUrl('create', [
      'room_id' => 6
    ]) }}"
    class="flex items-center justify-center"
    style="grid-column: 1 / 2; grid-row: 14 / 19; background-color: #D9EBD3;"
  >
    <span class="font-bold text-xs text-center">會議室 6 (複合式空間)</span>
  </a>
  <a
    href="{{ \App\Filament\Resources\EventResource::getUrl('create', [
      'room_id' => 7
    ]) }}"
    class="flex items-center justify-center"
    style="grid-column: 1 / 2; grid-row: 19 / 25; background-color: #D9EBD3;"
  >
    <span class="font-bold text-xs text-center">會議室 7 (複合式空間)</span>
  </a>
  <div class="flex items-center justify-center" style="grid-column: 2 / 9; grid-row: 5 / 25; background-color: #FFF3CC;">
    <span class="font-bold text-xs text-center">進駐區</span>
  </div>
  <div class="flex items-center justify-center" style="grid-column: 9 / 13; grid-row: 3 / 25; background-color: #D0E2F3;">
    <span class="font-bold text-xs text-center">公共區＆學員區</span>
  </div>
  <a
    href="{{ \App\Filament\Resources\EventResource::getUrl('create', [
      'room_id' => 1
    ]) }}"
    class="flex items-center justify-center"
    style="grid-column: 13 / 14; grid-row: 8 / 15; background-color: #D9EBD3;"
  >
    <span class="font-bold text-xs text-center">會議室 1 (大型)</span>
  </a>
</div>