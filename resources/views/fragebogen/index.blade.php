<x-app-layout>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="flex-grow flex flex-col w-full relative" x-data="fragebogen({{ isset($sharedResult) ? json_encode($sharedResult) : 'null' }}, {{ $characterQuestions->toJson() }}, {{ $partnerQuestions->toJson() }}, {{ $showUniverse ? 'true' : 'false' }})">
        <div class="flex-grow flex flex-col w-full h-full">
                <style>
                    [x-cloak] { display: none !important; }
                    @media (hover: hover) {
                        .hover-trigger:hover {
                            border-color: var(--tw-hover-border) !important;
                            transform: translateY(-4px) !important;
                            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1) !important;
                        }
                        .hover-trigger:hover .hover-text {
                            color: var(--tw-hover-text) !important;
                        }
                    }
                    .select-none { user-select: none; -webkit-user-select: none; }
                </style>
                <div
                    class="flex-grow flex flex-col bg-white dark:bg-gray-800 transition-colors duration-300 relative select-none"
                    style="-webkit-tap-highlight-color: transparent;">

                <!-- Progress Bar -->
                <div class="absolute top-0 left-0 w-full h-2 bg-gray-100 dark:bg-gray-900 rounded-t-2xl overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-pink-500 to-purple-500 transition-all duration-500 ease-out"
                        :style="`width: ${progressPercentage}%`"></div>
                </div>

                <div
                    class="flex-grow p-8 sm:p-12 text-gray-900 dark:text-gray-100 flex flex-col items-center justify-center min-h-[600px] w-full relative overflow-hidden transition-colors duration-300">

                    <!-- STEP 1: Personal Info -->
                    <template x-if="step === 1">
                        <div x-transition:enter="transition ease-out duration-500 transform"
                            x-transition:enter-start="opacity-0 translate-x-8"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-300 transform absolute"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-8" class="w-full max-w-md w-full"
                            x-show="step === 1">
                            <h3
                                class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-500 mb-6 text-center">
                                {{ __('Let\'s Get Started') }}</h3>
                            <p class="text-gray-500 text-center mb-8">{{ __('Tell us a bit about yourself so we can tailor the experience.') }}</p>

                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('First Name or Nickname') }}</label>
                                    <input type="text" x-model="personalInfo.name" placeholder="E.g. Troy"
                                        class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm py-3 px-4 transition">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Identify as') }}</label>
                                        <div class="grid grid-cols-1 gap-2">
                                            <template x-for="gender in ['Male', 'Female', 'Other']">
                                                <button @click="personalInfo.gender = gender; $el.blur()"
                                                    :class="personalInfo.gender === gender ? 'bg-pink-100 dark:bg-pink-900/30 border-pink-500 text-pink-700 dark:text-pink-400 font-semibold' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                                                    class="py-2 border rounded-xl transition text-xs focus:outline-none transition-colors duration-200 active:scale-95 hover-trigger"
                                                    style="--tw-hover-border: #f9a8d4;">
                                                    <span x-text="@js(['Male' => __('Male'), 'Female' => __('Female'), 'Other' => __('Other')])[gender]"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <div>
                                         <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Looking for') }}</label>
                                        <div class="flex flex-col gap-2">
                                            <template x-for="seeking in ['Men', 'Women', 'Everyone']">
                                                <button @click="personalInfo.seeking = seeking; $el.blur()"
                                                    :class="personalInfo.seeking === seeking ? 'bg-purple-100 dark:bg-purple-900/30 border-purple-500 text-purple-700 dark:text-purple-400 font-semibold' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                                                     class="flex-1 py-2 border rounded-xl transition text-xs focus:outline-none transition-colors duration-200 active:scale-95 hover-trigger"
                                                     style="--tw-hover-border: #d8b4fe;">
                                                    <span x-text="@js(['Men' => __('Men'), 'Women' => __('Women'), 'Everyone' => __('Everyone')])[seeking]"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                     <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-3">{{ __('Choose your Universes') }}</label>
                                    <div class="grid grid-cols-2 gap-2 pr-1 pb-1">
                                         <template x-for="f in franchises">
                                            <button @click="toggleUniverse(f.id); $el.blur()"
                                                :class="personalInfo.selectedUniverses.includes(f.id) ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 text-indigo-700 dark:text-indigo-400' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'"
                                                class="flex flex-col items-center justify-center p-3 border-2 rounded-xl transition text-center group transition-colors duration-200 active:scale-95 hover-trigger"
                                                style="--tw-hover-border: #a5b4fc;">
                                                <span class="text-xl mb-1" x-text="f.emoji"></span>
                                                <span class="text-[10px] font-bold uppercase truncate w-full" x-text="f.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-4">
                                     <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-3">{{ __('Quiz Length') }}</label>
                                    <div class="grid grid-cols-2 gap-4">
                                         <button @click="personalInfo.quizLength = 'Short'; $el.blur()"
                                            :class="personalInfo.quizLength === 'Short' ? 'bg-pink-100 dark:bg-pink-900/30 border-pink-500 text-pink-700 dark:text-pink-400' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400'"
                                            class="flex flex-col items-center justify-center p-4 border-2 rounded-2xl transition shadow-sm group transition-colors duration-200 active:scale-95 hover-trigger"
                                            style="--tw-hover-border: #f472b6;">
                                             <span class="text-2xl mb-1 group-hover:scale-110 transition-transform">⚡</span>
                                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('Short') }} (5+5)</span>
                                        </button>
                                        <button @click="personalInfo.quizLength = 'Long'; $el.blur()"
                                            :class="personalInfo.quizLength === 'Long' ? 'bg-purple-100 dark:bg-purple-900/30 border-purple-500 text-purple-700 dark:text-purple-400' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400'"
                                            class="flex flex-col items-center justify-center p-4 border-2 rounded-2xl transition shadow-sm group transition-colors duration-200 active:scale-95 hover-trigger"
                                            style="--tw-hover-border: #c084fc;">
                                             <span class="text-2xl mb-1 group-hover:scale-110 transition-transform">📜</span>
                                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('Long') }} (10+10)</span>
                                        </button>
                                    </div>
                                </div>

                                <button @click="nextStep()" :disabled="!isStep1Valid"
                                    class="w-full mt-8 py-4 px-6 rounded-xl text-white font-bold text-lg shadow-lg hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="isStep1Valid ? 'bg-gradient-to-r from-pink-500 to-purple-500 hover:scale-[1.02]' : 'bg-gray-400'">
                                     {{ __('Dive In') }} ✨
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- STEP 2: Character Questions -->
                    <template x-if="step === 2">
                        <div x-transition:enter="transition ease-out duration-500 transform"
                            x-transition:enter-start="opacity-0 translate-x-8"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-300 transform absolute"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-8" class="w-full max-w-2xl text-center"
                            x-show="step === 2">
                            <span class="text-xs font-black uppercase tracking-[0.3em] text-pink-500 mb-2 block transition-all">{{ __('Who are you?') }}</span>
                            <template x-if="showUniverse && currentCharacterQuestion.universe">
                              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-pink-50 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400 border border-pink-100 dark:border-pink-800 mb-4 transition-all" x-text="currentCharacterQuestion.universe"></span>
                            </template>
                            <h3 class="text-2xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-10 leading-tight transition-all"
                                x-text="currentCharacterQuestion.text"></h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <template x-for="(option, index) in currentCharacterQuestion.options" :key="currentCharacterQuestion.id + '-' + index">
                                    <button @click="answerCharacterQuestion(option); $el.blur()"
                                        class="group relative overflow-hidden bg-white dark:bg-gray-800 border-2 border-gray-100 dark:border-gray-700 rounded-2xl p-6 text-left transition-all duration-300 shadow-sm focus:outline-none transform active:scale-95 hover-trigger"
                                        style="--tw-hover-border: #f472b6; --tw-hover-text: #db2777;">
                                        <div class="flex items-start gap-4">
                                            <template x-if="option.image">
                                                <img :src="option.image" alt="option image" class="w-16 h-16 rounded-xl object-cover shadow-sm border border-gray-200 dark:border-gray-600 transition-all">
                                            </template>
                                            <template x-if="!option.image">
                                                <div class="text-3xl" x-text="option.emoji"></div>
                                            </template>
                                            <div>
                                                <h4 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-1 transition hover-text"
                                                    x-text="option.label"></h4>
                                                <template x-if="option.description">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 transition" x-text="option.description"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- STEP 3: Partner Preferences Questions -->
                    <template x-if="step === 3">
                        <div x-transition:enter="transition ease-out duration-500 transform"
                            x-transition:enter-start="opacity-0 translate-x-8"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-300 transform absolute"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-8" class="w-full max-w-2xl text-center"
                            x-show="step === 3">
                            <span class="text-xs font-black uppercase tracking-[0.3em] text-purple-500 mb-2 block transition-all">{{ __('What do you want?') }}</span>
                            <template x-if="showUniverse && currentPartnerQuestion.universe">
                              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-800 mb-4 transition-all" x-text="currentPartnerQuestion.universe"></span>
                            </template>
                            <h3 class="text-2xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-10 leading-tight transition-all"
                                x-text="currentPartnerQuestion.text"></h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                <template x-for="(option, index) in currentPartnerQuestion.options" :key="currentPartnerQuestion.id + '-' + index">
                                    <button @click="answerPartnerQuestion(option); $el.blur()"
                                        class="group relative overflow-hidden bg-white dark:bg-gray-800 border-2 border-gray-100 dark:border-gray-700 rounded-2xl p-6 text-left transition-all duration-300 shadow-sm focus:outline-none transform active:scale-95 hover-trigger"
                                        style="--tw-hover-border: #c084fc; --tw-hover-text: #9333ea;">
                                        <div class="flex items-start gap-4">
                                            <template x-if="option.image">
                                                <img :src="option.image" alt="option image" class="w-16 h-16 rounded-xl object-cover shadow-sm border border-gray-200 dark:border-gray-600 transition-all">
                                            </template>
                                            <template x-if="!option.image">
                                                <div class="text-3xl" x-text="option.emoji"></div>
                                            </template>
                                            <div>
                                                <h4 class="font-bold text-gray-800 dark:text-gray-100 text-lg mb-1 transition hover-text"
                                                    x-text="option.label"></h4>
                                                <template x-if="option.description">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 transition" x-text="option.description"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- STEP 4: Results -->
                    <template x-if="step === 4">
                        <div x-transition:enter="transition ease-out duration-700 transform lg:delay-300"
                            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="w-full"
                            x-show="step === 4">

                            <div class="text-center mb-10">
                                <h2
                                    class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-purple-600 mb-4 transition-all">
                                    {{ __('The Verdict') }}</h2>
                                <p class="text-lg text-gray-600 dark:text-gray-400 transition-all">{{ __('Based on our highly scientific algorithm,') }} <span
                                        class="font-bold text-gray-900 dark:text-white" x-text="personalInfo.name"></span>, {{ __('here is your dating profile breakdown!') }}</p>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                                <!-- LEFT COL: Chart -->
                                <div
                                    class="bg-gray-50 dark:bg-gray-900/40 p-6 rounded-3xl shadow-inner relative flex flex-col items-center justify-center min-h-[400px] border border-transparent dark:border-gray-700 transition-all">
                                    <h3 class="text-sm font-bold uppercase text-gray-500 dark:text-gray-400 mb-6 tracking-widest">{{ __('Your Vibe Matrix') }}</h3>
                                    <div class="w-full max-w-md w-full relative">
                                        <canvas id="spiderChart"></canvas>
                                    </div>
                                </div>

                                <!-- RIGHT COL: Analysis & Mapping -->
                                <div class="flex flex-col justify-center space-y-8">
                                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-[2px] rounded-3xl shadow-xl transition-all">
                                        <div class="bg-white dark:bg-gray-800 rounded-[calc(1.5rem-2px)] p-6 transition-all duration-300">
                                            <div class="flex items-center gap-4 mb-4">
                                                <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center text-3xl shadow-inner transition-all" x-text="getCharEmoji()"></div>
                                                <div>
                                                    <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-400 dark:text-indigo-300">{{ __('Character Match') }}</span>
                                                    <h3 class="text-2xl font-black text-gray-900 dark:text-white leading-tight transition-all" x-text="mappedCharacter"></h3>
                                                </div>
                                            </div>
                                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed italic border-l-4 border-indigo-100 dark:border-indigo-900/50 pl-4 transition-all" x-text="characterAnalysis"></p>
                                        </div>
                                    </div>

                                    <div class="bg-pink-50/50 dark:bg-pink-900/10 p-6 rounded-2xl border border-pink-100 dark:border-pink-900/30 transition-all">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xl">💪</span>
                                            <h3 class="text-lg font-bold text-pink-900 dark:text-pink-300">{{ __('Greatest Strengths') }}</h3>
                                        </div>
                                        <p class="text-pink-800 dark:text-pink-400 leading-relaxed text-sm transition-all" x-text="strengthsSummary"></p>
                                    </div>

                                    <div class="bg-purple-50/50 dark:bg-purple-900/10 p-6 rounded-2xl border border-purple-100 dark:border-purple-900/30 transition-all">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xl">❤️</span>
                                            <h3 class="text-lg font-bold text-purple-900 dark:text-purple-300">{{ __('Your Perfect Match') }}</h3>
                                        </div>
                                        <p class="text-purple-800 dark:text-purple-400 leading-relaxed text-sm transition-all" x-text="perfectMatchSummary"></p>
                                    </div>

                                    <template x-if="shareUrl">
                                        <div class="bg-indigo-50 dark:bg-indigo-900/10 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-900/30 transition-all">
                                            <div class="flex items-center gap-3 mb-3">
                                                <span class="text-xl">🔗</span>
                                                <h3 class="text-lg font-bold text-indigo-900 dark:text-indigo-300">{{ __('Share your Result') }}</h3>
                                            </div>
                                            <div class="flex gap-2">
                                                <input type="text" readonly :value="shareUrl" class="flex-1 text-xs border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-gray-600 dark:text-gray-300 focus:ring-0 transition-all">
                                                <button @click="navigator.clipboard.writeText(shareUrl); alert('Link copied!')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 transition">Copy</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="text-center mt-12">
                                <button @click="reset()"
                                    class="text-gray-500 hover:text-pink-600 font-medium transition underline underline-offset-4">
                                    Start Over
                                </button>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </div>

    <!-- Alpine State Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fragebogen', (initialData = null, characterQuestions = [], partnerQuestions = [], showUniverse = true) => ({
                step: 1,
                characterQuestionIndex: 0,
                partnerQuestionIndex: 0,
                isShared: false,
                shareUrl: '',
                fullResults: [],
                allCharacterQuestions: characterQuestions,
                allPartnerQuestions: partnerQuestions,
                showUniverse: showUniverse,

                init() {
                    if (initialData) {
                        this.isShared = true;
                        this.personalInfo = {
                            name: initialData.name,
                            gender: initialData.gender,
                            seeking: initialData.seeking,
                            franchise: initialData.franchise,
                            selectedUniverses: [initialData.franchise]
                        };
                        this.traits = initialData.traits;
                        this.partnerTraits = initialData.partner_traits;
                        this.mappedCharacter = initialData.mapped_character;
                        this.shareUrl = window.location.href;
                        
                        this.step = 4;
                        this.generateSummaries();
                        setTimeout(() => this.renderChart(), 500);
                    }
                },

                personalInfo: {
                    name: '',
                    gender: '',
                    seeking: '',
                    selectedUniverses: ['Wildcard'],
                    quizLength: 'Short'
                },

                franchises: [
                    { id: 'High School Musical', name: 'High School Musical', emoji: '🎤' },
                    { id: 'Harry Potter', name: 'Harry Potter', emoji: '🪄' },
                    { id: 'Phineas & Ferb', name: 'Phineas & Ferb', emoji: '🛠️' },
                    { id: 'Lord of the Rings', name: 'Lord of the Rings', emoji: '🏹' },
                    { id: 'Bernd das Brot', name: 'Bernd das Brot', emoji: '🍞' },
                    { id: 'Wildcard', name: 'Multiversum (Mixed)', emoji: '✨' }
                ],

                characterMapping: {
                    'High School Musical': {
                        Social: { name: 'Troy Bolton', text: 'Du bist das Herz des Teams. Du jonglierst ständig zwischen Erwartungen und deinem Herzen.' },
                        Romantic: { name: 'Gabriella Montez', text: 'Du bist tiefgründig und suchst nach echter Verbindung. "Breaking Free" ist dein Lebensmotto.' },
                        Organized: { name: 'Sharpay Evans', text: 'Du weißt, was du willst, und hast den 5-Jahres-Plan schon fertig.' },
                        Creative: { name: 'Ryan Evans', text: 'Du bist das unterschätzte Genie hinter den Kulissen. Ohne deine Vision gäbe es keine Show.' },
                        Logical: { name: 'Taylor McKessie', text: 'Du bist der Kopf der Gruppe. Während andere tanzen, hast du schon die Weltrettung geplant.' }
                    },
                    'Harry Potter': {
                        Adventurous: { name: 'Harry Potter', text: 'Du handelst nach Instinkt und stürzt dich in jedes Abenteuer – oft ohne Plan, aber mit viel Herz.' },
                        Logical: { name: 'Hermine Granger', text: 'Du bist brillant, strukturiert und die Rettung in jeder Notlage.' },
                        Homebody: { name: 'Ron Weasley', text: 'Loyalität, ein gemütlicher Pulli und ein gutes Essen sind dir wichtiger als jeder Ruhm.' },
                        Creative: { name: 'Luna Lovegood', text: 'Du siehst Nargel, wo andere nur Luft sehen. Deine Einzigartigkeit ist deine Stärke.' },
                        Organized: { name: 'Draco Malfoy', text: 'Du achtest auf deinen Ruf und spielst das Spiel des Lebens nach deinen eigenen Regeln.' }
                    },
                    'Phineas & Ferb': {
                        Creative: { name: 'Phineas Flynn', text: '"Ferb, ich weiß, was wir heute tun!" Dein Optimismus kennt keine Grenzen.' },
                        Logical: { name: 'Ferb Fletcher', text: 'Du sagst nicht viel, aber wenn du etwas sagst, hat es Gewicht. Ein Macher der leisen Töne.' },
                        Spontaneous: { name: 'Perry (Agent P)', text: 'Du führst ein Doppelleben zwischen totaler Entspannung und hochriskanten Geheimmissionen.' },
                        Organized: { name: 'Candace Flynn', text: 'Du liebst Ordnung und willst, dass alle anderen sich an die Regeln halten.' },
                        Romantic: { name: 'Dr. Doofenshmirtz', text: 'Deine tragische Hintergrundgeschichte hat dich geprägt, aber dein Erfindergeist ist unzerstörbar!' }
                    },
                    'Lord of the Rings': {
                        Adventurous: { name: 'Aragorn', text: 'Ein Wanderer, der keine Angst vor der Dunkelheit hat. Eine geborene Führungspersönlichkeit.' },
                        Homebody: { name: 'Samweis Gamdschie', text: 'Du bist der Fels in der Brandung. Ohne dich würde niemand das Ziel erreichen.' },
                        Social: { name: 'Pippin & Merry', text: 'Du bringst Licht in dunkle Zeiten und weißt, dass ein zweites Frühstück lebensnotwendig ist.' },
                        Logical: { name: 'Elrond / Galadriel', text: 'Du blickst über den Tellerrand hinaus und planst in Jahrhunderten, nicht in Tagen.' },
                        Romantic: { name: 'Arwen', text: 'Für die wahre Liebe würdest du alles aufgeben – sogar deine Unsterblichkeit.' }
                    },
                    'Bernd das Brot': {
                        Homebody: { name: 'Bernd das Brot', text: 'Du liebst deine Raufasertapete und hast eine Abneigung gegen unnötigen Enthusiasmus.' },
                        Social: { name: 'Chili das Schaf', text: 'Du bist das totale Gegenteil von Bernd: Hyperaktiv, laut und immer auf der Suche nach dem nächsten Stunt.' },
                        Creative: { name: 'Briegel der Busch', text: 'Du bist ein genialer Erfinder, aber deine Experimente enden meistens in einer Explosion.' }
                    }
                },

                mappedCharacter: '',
                characterAnalysis: '',

                traits: {
                    Spontaneous: 0,
                    Homebody: 0,
                    Adventurous: 0,
                    Romantic: 0,
                    Logical: 0,
                    Organized: 0,
                    Social: 0,
                    Creative: 0
                },

                partnerTraits: {
                    Spontaneous: 0,
                    Homebody: 0,
                    Adventurous: 0,
                    Romantic: 0,
                    Logical: 0,
                    Organized: 0,
                    Social: 0,
                    Creative: 0
                },

                chartInstance: null,



                characterQuestions: [],
                partnerQuestions: [],

                // ------------------
                //  Getters
                // ------------------
                get isStep1Valid() {
                    return this.personalInfo.name.length > 0 &&
                        this.personalInfo.gender !== '' &&
                        this.personalInfo.seeking !== '' &&
                        this.personalInfo.selectedUniverses.length > 0;
                },

                get currentCharacterQuestion() {
                    return this.characterQuestions[this.characterQuestionIndex];
                },

                get currentPartnerQuestion() {
                    return this.partnerQuestions[this.partnerQuestionIndex];
                },

                get progressPercentage() {
                    const totalSteps = 1 + this.characterQuestions.length + this.partnerQuestions.length + 1; // 1 (start) + char qs + part qs + 1 (result)
                    let currentGlobalStep = 0;
                    if (this.step === 1) currentGlobalStep = 1;
                    else if (this.step === 2) currentGlobalStep = 1 + this.characterQuestionIndex + 1;
                    else if (this.step === 3) currentGlobalStep = 1 + this.characterQuestions.length + this.partnerQuestionIndex + 1;
                    else if (this.step === 4) currentGlobalStep = totalSteps;

                    return (currentGlobalStep / totalSteps) * 100;
                },

                // ------------------
                //  Actions
                // ------------------
                nextStep() {
                    if (this.step === 1 && this.isStep1Valid) {
                        const shuffle = (array) => {
                            for (let i = array.length - 1; i > 0; i--) {
                                const j = Math.floor(Math.random() * (i + 1));
                                [array[i], array[j]] = [array[j], array[i]];
                            }
                            return array;
                        };

                        // Select and filter questions
                        let charPool = [...this.allCharacterQuestions];
                        let partPool = [...this.allPartnerQuestions];

                        if (!this.personalInfo.selectedUniverses.includes('Wildcard')) {
                            charPool = charPool.filter(q => !q.universe || this.personalInfo.selectedUniverses.includes(q.universe));
                            partPool = partPool.filter(q => !q.universe || this.personalInfo.selectedUniverses.includes(q.universe));
                        }

                        // If pool too small after filtering, fallback to wildcards or all
                        if (charPool.length < 5) charPool = [...this.allCharacterQuestions];
                        if (partPool.length < 5) partPool = [...this.allPartnerQuestions];

                        const counts = this.personalInfo.quizLength === 'Short' ? 5 : 10;
                        this.characterQuestions = shuffle(charPool).slice(0, counts);
                        this.partnerQuestions = shuffle(partPool).slice(0, counts);
                        
                        // Pick the primary universe for the final mapping (first non-wildcard selected)
                        this.personalInfo.franchise = this.personalInfo.selectedUniverses.find(u => u !== 'Wildcard') || 'Wildcard';

                        this.step = 2;
                    }
                },

                toggleUniverse(id) {
                    if (id === 'Wildcard') {
                        this.personalInfo.selectedUniverses = ['Wildcard'];
                        return;
                    }

                    // Remove wildcard if specific one selected
                    this.personalInfo.selectedUniverses = this.personalInfo.selectedUniverses.filter(u => u !== 'Wildcard');

                    if (this.personalInfo.selectedUniverses.includes(id)) {
                        this.personalInfo.selectedUniverses = this.personalInfo.selectedUniverses.filter(u => u !== id);
                        if (this.personalInfo.selectedUniverses.length === 0) this.personalInfo.selectedUniverses = ['Wildcard'];
                    } else {
                        this.personalInfo.selectedUniverses.push(id);
                    }
                },

                answerCharacterQuestion(option) {
                    // track history
                    this.fullResults.push({
                        type: 'character',
                        question: this.currentCharacterQuestion.text,
                        answer: option.label,
                        traits: option.traits
                    });

                    // add traits
                    for (const [trait, value] of Object.entries(option.traits)) {
                        this.traits[trait] += value;
                    }

                    if (this.characterQuestionIndex < this.characterQuestions.length - 1) {
                        this.characterQuestionIndex++;
                    } else {
                        this.step = 3;
                    }
                },

                answerPartnerQuestion(option) {
                    // track history
                    this.fullResults.push({
                        type: 'partner',
                        question: this.currentPartnerQuestion.text,
                        answer: option.label,
                        traits: option.traits
                    });

                    // add traits
                    for (const [trait, value] of Object.entries(option.traits)) {
                        this.partnerTraits[trait] += value;
                    }

                    if (this.partnerQuestionIndex < this.partnerQuestions.length - 1) {
                        this.partnerQuestionIndex++;
                    } else {
                        this.step = 4;
                        this.generateSummaries();
                        this.saveResult();
                        // use specific delay to render chart because of animation
                        setTimeout(() => this.renderChart(), 400);
                    }
                },

                async saveResult() {
                    if (this.isShared) return;

                    try {
                        const response = await fetch('{{ route('fragebogen.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ...this.personalInfo,
                                mapped_character: this.mappedCharacter,
                                traits: this.traits,
                                partner_traits: this.partnerTraits,
                                full_results: this.fullResults
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.shareUrl = data.share_url;
                        }
                    } catch (error) {
                        console.error('Failed to save result:', error);
                    }
                },

                // ------------------
                //  Analysis & Chart
                // ------------------
                strengthsSummary: "",
                weaknessesSummary: "",
                perfectMatchSummary: "",

                generateSummaries() {
                    let sortedTraits = Object.entries(this.traits).sort((a, b) => b[1] - a[1]);
                    let topTrait = sortedTraits[0][0];
                    let bottomTrait = sortedTraits[sortedTraits.length - 1][0];

                    // Character Mapping
                    const franchise = this.personalInfo.franchise;
                    if (franchise === 'Wildcard') {
                        const wildcards = {
                            Adventurous: 'Harry Potter', Organized: 'Sharpay Evans', Homebody: 'Bernd das Brot', 
                            Social: 'Troy Bolton', Creative: 'Phineas Flynn', Logical: 'Hermine Granger',
                            Spontaneous: 'Perry (Agent P)', Romantic: 'Arwen'
                        };
                        this.mappedCharacter = wildcards[topTrait] || 'Mystery Legend';
                        this.characterAnalysis = `Ein wahrer Multiversum-Hybrid! Du hast den Geist von ${this.mappedCharacter}, kombiniert mit erstklassiger ${topTrait}-Energie und einer Prise Magie.`;
                    } else {
                        const mapping = this.characterMapping[franchise][topTrait] || Object.values(this.characterMapping[franchise])[0];
                        this.mappedCharacter = mapping.name;
                        this.characterAnalysis = mapping.text;
                    }

                    const strengthTexts = {
                        Spontaneous: "{{ __('You bring energy everywhere. Life with you is never boring!') }}",
                        Homebody: "{{ __('You are an incredibly calming presence. You make any place feel like home.') }}",
                        Adventurous: "{{ __('Your zest for life is contagious. You are always ready to try new things.') }}",
                        Romantic: "{{ __('You never overlook the little things. You are deep and value real connections.') }}",
                        Logical: "{{ __('You are the anchor in the storm. Your ability to think clearly under pressure is reliable.') }}",
                        Organized: "{{ __('You have your life under control. Partners love how structured and reliable you are.') }}",
                        Social: "{{ __('You are the heart of every party! You could talk to a wall and make it laugh.') }}",
                        Creative: "{{ __('Your mind is a wonderful place. You bring creative solutions to all aspects of life.') }}"
                    };

                    this.strengthsSummary = strengthTexts[topTrait] || "{{ __('You are an incredibly versatile person with a lot of potential.') }}";

                    // Perfect partner
                    let sortedPartnerTraits = Object.entries(this.partnerTraits).sort((a, b) => b[1] - a[1]);
                    let topPartnerTrait = sortedPartnerTraits[0][0];

                    const matchTexts = {
                        Spontaneous: "{{ __('You are looking for someone who is pure chaos in the best sense.') }}",
                        Homebody: "{{ __('Your perfect match is someone whose ideal Friday night consists of sweatpants and pizza.') }}",
                        Adventurous: "{{ __('You need an explorer. Someone who lures you out of your comfort zone.') }}",
                        Romantic: "{{ __('You are looking for absolute romance. Your match should bring flowers just because.') }}",
                        Logical: "{{ __('You need a problem solver. Your partner should be grounded and emotionally intelligent.') }}",
                        Organized: "{{ __('You love someone who takes the lead and has a 5-year plan.') }}",
                        Social: "{{ __('You want a partner with Golden Retriever Energy who wraps everyone around their finger.') }}",
                        Creative: "{{ __('Your perfect match is an artist at heart who sees the world with different eyes.') }}"
                    };

                    this.perfectMatchSummary = matchTexts[topPartnerTrait] || "{{ __('You are looking for someone who complements you perfectly!') }}";
                },

                renderChart() {
                    const canvas = document.getElementById('spiderChart');
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');
                    const labelMapping = {
                        Spontaneous: "{{ __('Spontaneous') }}",
                        Homebody: "{{ __('Homebody') }}",
                        Adventurous: "{{ __('Adventurous') }}",
                        Romantic: "{{ __('Romantic') }}",
                        Logical: "{{ __('Logical') }}",
                        Organized: "{{ __('Organized') }}",
                        Social: "{{ __('Social') }}",
                        Creative: "{{ __('Creative') }}"
                    };
                    const labels = Object.keys(this.traits).map(t => labelMapping[t] || t);
                    const dataPoints = Object.values(this.traits);

                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                    }

                    const isDark = document.documentElement.classList.contains('dark') || 
                                   (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    
                    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                    const labelColor = isDark ? '#9CA3AF' : '#4B5563';

                    this.chartInstance = new Chart(ctx, {
                        type: 'radar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Your Personality Vibe',
                                data: dataPoints,
                                backgroundColor: isDark ? 'rgba(244, 114, 182, 0.25)' : 'rgba(236, 72, 153, 0.2)', 
                                borderColor: isDark ? 'rgba(244, 114, 182, 1)' : 'rgba(236, 72, 153, 1)',
                                pointBackgroundColor: 'rgba(168, 85, 247, 1)', 
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgba(168, 85, 247, 1)',
                                borderWidth: 3,
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                r: {
                                    angleLines: {
                                        color: gridColor
                                    },
                                    grid: {
                                        color: gridColor
                                    },
                                    pointLabels: {
                                        font: {
                                            family: "'Figtree', sans-serif",
                                            size: 13,
                                            weight: 'bold'
                                        },
                                        color: labelColor
                                    },
                                    ticks: {
                                        display: false 
                                    },
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false 
                                },
                                tooltip: {
                                    backgroundColor: isDark ? 'rgba(31, 41, 55, 0.9)' : 'rgba(17, 24, 39, 0.8)',
                                    titleFont: { family: "'Figtree', sans-serif", size: 14 },
                                    bodyFont: { family: "'Figtree', sans-serif", size: 13 },
                                    padding: 12,
                                    cornerRadius: 8,
                                }
                            }
                        }
                    });
                },

                reset() {
                    this.step = 1;
                    this.characterQuestionIndex = 0;
                    this.partnerQuestionIndex = 0;

                    // Reset traits
                    for (let key in this.traits) this.traits[key] = 0;
                    for (let key in this.partnerTraits) this.partnerTraits[key] = 0;

                    this.personalInfo = {
                        name: '',
                        gender: '',
                        seeking: '',
                        franchise: 'Wildcard'
                    };
                },

                getCharEmoji() {
                    const emojis = { 
                        'Troy Bolton': '🏀', 'Gabriella Montez': '🎤', 'Sharpay Evans': '✨', 'Ryan Evans': '🎹', 'Taylor McKessie': '📚',
                        'Harry Potter': '⚡', 'Hermine Granger': '📜', 'Ron Weasley': '🍗', 'Luna Lovegood': '👓', 'Draco Malfoy': '🐍',
                        'Phineas Flynn': '🛠️', 'Ferb Fletcher': '🏗️', 'Perry (Agent P)': '🕵️', 'Candace Flynn': '📱', 'Dr. Doofenshmirtz': '🤖',
                        'Aragorn': '⚔️', 'Samweis Gamdschie': '🍳', 'Pippin & Merry': '🍺', 'Elrond / Galadriel': '🧝', 'Arwen': '💍',
                        'Bernd das Brot': '🍞', 'Chili das Schaf': '🌶️', 'Briegel der Busch': '🌿'
                    };
                    return emojis[this.mappedCharacter] || '👤';
                }
            }));
        });
    </script>
</x-app-layout>