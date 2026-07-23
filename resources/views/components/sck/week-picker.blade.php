@props(['name' => 'week', 'value', 'accent' => 'cyan'])

<div class="sck-week-picker" :class="{ 'is-open': open }" x-data="sckWeekPicker(@js($value))" @click.outside="open = false" @keydown.escape.window="open = false">
    <input type="hidden" name="{{ $name }}" x-model="selected">

    <button type="button" class="sck-week-picker__arrow" @click="shift(-1)" aria-label="Vorherige Woche">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
    </button>

    <button type="button" class="sck-week-picker__current" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="dialog" aria-label="Woche auswählen">
        <i class="fa-regular fa-calendar-days sck-week-picker__icon text-{{ $accent }}-400" aria-hidden="true"></i>
        <span class="sck-week-picker__copy">
            <span class="sck-week-picker__week" x-text="weekLabel"></span>
            <span class="sck-week-picker__range" x-text="rangeLabel"></span>
        </span>
        <i class="fa-solid fa-chevron-down sck-week-picker__chevron" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
    </button>

    <button type="button" class="sck-week-picker__arrow" @click="shift(1)" aria-label="Nächste Woche">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </button>

    <div class="sck-week-picker__menu" x-show="open" x-cloak x-transition.origin.top.left role="dialog" aria-label="Woche auswählen">
        <div class="sck-week-picker__menu-header">
            <button type="button" class="sck-week-picker__month-arrow" @click="changeMonth(-1)" aria-label="Vorheriger Monat"><i class="fa-solid fa-chevron-left"></i></button>
            <strong x-text="monthLabel"></strong>
            <button type="button" class="sck-week-picker__month-arrow" @click="changeMonth(1)" aria-label="Nächster Monat"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <button type="button" class="sck-week-picker__today" @click="selectCurrentWeek()">Diese Woche</button>
        <div class="sck-week-picker__weeks">
            <template x-for="week in weeksForMonth" :key="week.value">
                <button type="button" class="sck-week-picker__week-option" :class="{ 'is-selected': week.value === selected, 'is-current': week.value === currentWeek }" @click="choose(week.value, true)">
                    <span class="sck-week-picker__week-number" x-text="'KW ' + week.number"></span>
                    <span x-text="week.range"></span>
                </button>
            </template>
        </div>
    </div>
</div>
