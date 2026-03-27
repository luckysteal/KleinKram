<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DatingQuestion;

class DatingQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DatingQuestion::truncate();

        $characterQuestions = [
            [
                "text" => "Phineas & Ferb: Erster Ferientag?",
                "options" => [
                    ["emoji" => "🛠️", "label" => "Achterbahn bauen!", "traits" => ["Creative" => 3, "Spontaneous" => 2]],
                    ["emoji" => "🏗️", "label" => "Strategie planen", "traits" => ["Organized" => 3]],
                    ["emoji" => "🕵️", "label" => "Eigenes Ding machen", "traits" => ["Adventurous" => 2]],
                    ["emoji" => "📱", "label" => "Mama petzen", "traits" => ["Logical" => 2]],
                ]
            ],
            [
                "text" => "Bernd das Brot: Stress-Level?",
                "options" => [
                    ["emoji" => "🍞", "label" => "Raufasertapete starren", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🌋", "label" => "\"MIST!\" schreien", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🧤", "label" => "In die Box zurückziehen", "traits" => ["Homebody" => 2]],
                    ["emoji" => "📺", "label" => "Ironisch mittanzen", "traits" => ["Social" => 2]],
                ]
            ],
            [
                "text" => "HP: Der sprechende Hut?",
                "options" => [
                    ["emoji" => "🦁", "label" => "Mut & Ritterlichkeit", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🦅", "label" => "Wissen & Weisheit", "traits" => ["Logical" => 3]],
                    ["emoji" => "🦡", "label" => "Geduld & Loyalität", "traits" => ["Romantic" => 2, "Homebody" => 1]],
                    ["emoji" => "🐍", "label" => "Ehrgeiz & Stolz", "traits" => ["Organized" => 2]],
                ]
            ],
            [
                "text" => "HSM: Dein Spirit Animal?",
                "options" => [
                    ["emoji" => "🏀", "label" => "Troy (Teamplayer)", "traits" => ["Social" => 3]],
                    ["emoji" => "🎤", "label" => "Gabriella (Träumerin)", "traits" => ["Romantic" => 3]],
                    ["emoji" => "✨", "label" => "Sharpay (Fokus!)", "traits" => ["Organized" => 2]],
                    ["emoji" => "🎹", "label" => "Ryan (Kreativ-Genie)", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "LotR: Dein Platz in Mittelerde?",
                "options" => [
                    ["emoji" => "🏡", "label" => "Das Auenland (Essen!)", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🏰", "label" => "Gondor (Pflicht)", "traits" => ["Logical" => 2]],
                    ["emoji" => "🏹", "label" => "Düsterwald (Natur)", "traits" => ["Adventurous" => 2]],
                    ["emoji" => "🌋", "label" => "Schicksalsberg (Action)", "traits" => ["Spontaneous" => 2]],
                ]
            ],
            [
                "text" => "Dr. Doofenshmirtz: Dein Plan?",
                "options" => [
                    ["emoji" => "🤖", "label" => "Ein -Inator bauen!", "traits" => ["Creative" => 3]],
                    ["emoji" => "🌍", "label" => "Die Tri-State-Area regieren", "traits" => ["Organized" => 2]],
                    ["emoji" => "🎭", "label" => "Eine tragische Backstory", "traits" => ["Romantic" => 2]],
                    ["emoji" => "🧨", "label" => "Chaos verursachen", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "HP: Dein Lieblings-Fach?",
                "options" => [
                    ["emoji" => "🧪", "label" => "Zaubertränke (Präzision)", "traits" => ["Organized" => 3]],
                    ["emoji" => "🧹", "label" => "Fliegen (Freiheit)", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "📜", "label" => "Geschichte (Fakten)", "traits" => ["Logical" => 3]],
                    ["emoji" => "🦄", "label" => "Tierwesen (Empathie)", "traits" => ["Romantic" => 2]],
                ]
            ],
            [
                "text" => "Bernd: Dein Samstagabend?",
                "options" => [
                    ["emoji" => "📺", "label" => "Schlechte Filme gucken", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🛋️", "label" => "Alleine im Dunkeln sitzen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🛒", "label" => "Wocheneinkauf planen", "traits" => ["Organized" => 3]],
                    ["emoji" => "🚪", "label" => "Haus verlassen (Niemals!)", "traits" => ["Homebody" => 1]],
                ]
            ],
            [
                "text" => "LotR: Ein Ring, sie zu knechten?",
                "options" => [
                    ["emoji" => "💍", "label" => "Ich behalte ihn (Macht)", "traits" => ["Logical" => 2]],
                    ["emoji" => "🌋", "label" => "Ich zerstöre ihn (Moral)", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "📦", "label" => "Ich verstecke ihn (Ruhe)", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🧙‍♂️", "label" => "Ich frage einen Experten", "traits" => ["Organized" => 2]],
                ]
            ],
            [
                "text" => "HSM: Audition-Time!",
                "options" => [
                    ["emoji" => "🌟", "label" => "Ich bin der Star", "traits" => ["Social" => 3]],
                    ["emoji" => "🤝", "label" => "Ich helfe beim Setbau", "traits" => ["Organized" => 2]],
                    ["emoji" => "📝", "label" => "Ich schreibe das Skript", "traits" => ["Creative" => 3]],
                    ["emoji" => "🍿", "label" => "Ich gucke nur zu", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "Phineas: Candace erwischt dich!",
                "options" => [
                    ["emoji" => "🏃‍♂️", "label" => "Schnell wegrennen", "traits" => ["Spontaneous" => 2]],
                    ["emoji" => "🗣️", "label" => "Rausreden (Logik)", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎨", "label" => "Es als \"Kunst\" tarnen", "traits" => ["Creative" => 2]],
                    ["emoji" => "🤷‍♂️", "label" => "Cool bleiben", "traits" => ["Social" => 1]],
                ]
            ],
            [
                "text" => "HP: Patronus-Gestalt?",
                "options" => [
                    ["emoji" => "🐕", "label" => "Ein treuer Hund", "traits" => ["Romantic" => 2]],
                    ["emoji" => "🦅", "label" => "Ein stolzer Adler", "traits" => ["Logical" => 2]],
                    ["emoji" => "🦦", "label" => "Ein verspielter Otter", "traits" => ["Creative" => 2]],
                    ["emoji" => "🦌", "label" => "Ein würdevoller Hirsch", "traits" => ["Social" => 2]],
                ]
            ],
            [
                "text" => "LotR: Zweites Frühstück?",
                "options" => [
                    ["emoji" => "🥞", "label" => "Ja, unbedingt!", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🍎", "label" => "Nein, wir müssen weiter", "traits" => ["Organized" => 2]],
                    ["emoji" => "☕", "label" => "Nur Kaffee", "traits" => ["Logical" => 1]],
                    ["emoji" => "🥘", "label" => "Ich koche für alle", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Das verlassene Museum?",
                "options" => [
                    ["emoji" => "🔦", "label" => "Erkunden gehen", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🏛️", "label" => "Exponate sortieren", "traits" => ["Organized" => 3]],
                    ["emoji" => "🎨", "label" => "Ein Bild malen", "traits" => ["Creative" => 3]],
                    ["emoji" => "🚪", "label" => "Draußen warten", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "HP: Dein Irrwicht (Angst)?",
                "options" => [
                    ["emoji" => "🕷️", "label" => "Spinnen (Physisch)", "traits" => ["Adventurous" => 1]],
                    ["emoji" => "📉", "label" => "Versagen (Leistung)", "traits" => ["Logical" => 2]],
                    ["emoji" => "🕯️", "label" => "Einsamkeit (Sozial)", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🎭", "label" => "Peinlichkeit (Ego)", "traits" => ["Social" => 2]],
                ]
            ],
            [
                "text" => "LotR: Elb oder Zwerg?",
                "options" => [
                    ["emoji" => "🏹", "label" => "Elb (Ästhetik/Natur)", "traits" => ["Creative" => 2]],
                    ["emoji" => "⚒️", "label" => "Zwerg (Handwerk/Bier)", "traits" => ["Social" => 2]],
                    ["emoji" => "🧙‍♂️", "label" => "Zauberer (Wissen)", "traits" => ["Logical" => 3]],
                    ["emoji" => "🥗", "label" => "Hobbit (Gemütlichkeit)", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Ein sonniger Tag?",
                "options" => [
                    ["emoji" => "☀️", "label" => "Vorhang zu ziehen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🍦", "label" => "Eis essen (alleine)", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🧺", "label" => "Picknick (gezwungen)", "traits" => ["Social" => 1]],
                    ["emoji" => "🚶‍♂️", "label" => "Schatten suchen", "traits" => ["Logical" => 2]],
                ]
            ],
            [
                "text" => "HSM: Pausen-Aktivität?",
                "options" => [
                    ["emoji" => "🏀", "label" => "Körbe werfen", "traits" => ["Adventurous" => 2]],
                    ["emoji" => "📖", "label" => "In der Bio-Ecke lernen", "traits" => ["Logical" => 3]],
                    ["emoji" => "💃", "label" => "Tanzschritte üben", "traits" => ["Creative" => 3]],
                    ["emoji" => "🍎", "label" => "In der Mensa tratschen", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Perry ist weg!",
                "options" => [
                    ["emoji" => "🕵️‍♂️", "label" => "Detektiv-Ausrüstung an", "traits" => ["Adventurous" => 2]],
                    ["emoji" => "🛰️", "label" => "Satellitensuche starten", "traits" => ["Logical" => 3]],
                    ["emoji" => "📢", "label" => "Flugblätter verteilen", "traits" => ["Social" => 2]],
                    ["emoji" => "🍪", "label" => "Er kommt sicher zum Essen", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "HP: Der Spiegel Nerhegeb?",
                "options" => [
                    ["emoji" => "🏆", "label" => "Ruhm & Erfolg", "traits" => ["Organized" => 2]],
                    ["emoji" => "🏠", "label" => "Ein sicheres Zuhause", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🕯️", "label" => "Eine große Liebe", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🌍", "label" => "Unendliche Freiheit", "traits" => ["Adventurous" => 3]],
                ]
            ],
            [
                "text" => "LotR: Weggabelung im Wald?",
                "options" => [
                    ["emoji" => "🗺️", "label" => "Karte studieren", "traits" => ["Logical" => 3]],
                    ["emoji" => "🧭", "label" => "Bauchgefühl vertrauen", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🐾", "label" => "Spuren lesen", "traits" => ["Adventurous" => 2]],
                    ["emoji" => "🧙‍♂️", "label" => "Auf Führung warten", "traits" => ["Organized" => 1]],
                ]
            ],
            [
                "text" => "HSM: Das Outfit für den Ball?",
                "options" => [
                    ["emoji" => "✨", "label" => "Glitzer & Glamour", "traits" => ["Social" => 3]],
                    ["emoji" => "🕴️", "label" => "Klassisch & Schlicht", "traits" => ["Organized" => 2]],
                    ["emoji" => "🎨", "label" => "Etwas Selbstgemachtes", "traits" => ["Creative" => 3]],
                    ["emoji" => "👕", "label" => "Jeans & Sneaker", "traits" => ["Spontaneous" => 2]],
                ]
            ],
            [
                "text" => "Bernd: \"Tanz das Brot!\"",
                "options" => [
                    ["emoji" => "❌", "label" => "Niemals.", "traits" => ["Logical" => 2]],
                    ["emoji" => "🤖", "label" => "Nur mechanisch", "traits" => ["Organized" => 1]],
                    ["emoji" => "🎭", "label" => "Ironisch übertrieben", "traits" => ["Creative" => 2]],
                    ["emoji" => "🚪", "label" => "Raum verlassen", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "HP: Quidditch-Position?",
                "options" => [
                    ["emoji" => "🧹", "label" => "Sucher (Einzelgänger)", "traits" => ["Adventurous" => 2]],
                    ["emoji" => "🏏", "label" => "Treiber (Action)", "traits" => ["Spontaneous" => 2]],
                    ["emoji" => "🧤", "label" => "Hüter (Verantwortung)", "traits" => ["Organized" => 3]],
                    ["emoji" => "📣", "label" => "Kommentator (Reden)", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Dein \"Inator\"?",
                "options" => [
                    ["emoji" => "🧹", "label" => "Aufräum-Inator", "traits" => ["Organized" => 3]],
                    ["emoji" => "🥳", "label" => "Party-Inator", "traits" => ["Social" => 3]],
                    ["emoji" => "✈️", "label" => "Reise-Inator", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🛌", "label" => "Schlaf-Inator", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Phineas & Ferb: Sommerregen?",
                "options" => [
                    ["emoji" => "☔", "label" => "Ein Regen-Labor bauen", "traits" => ["Creative" => 3, "Spontaneous" => 2]],
                    ["emoji" => "🗓️", "label" => "Drinnen alles neu planen", "traits" => ["Organized" => 3]],
                    ["emoji" => "💃", "label" => "Im Regen tanzen", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🪟", "label" => "Nur vom Fenster aus schauen", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "Bernd das Brot: Nachbar klingelt?",
                "options" => [
                    ["emoji" => "🚪", "label" => "Nicht aufmachen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "📊", "label" => "Erst prüfen, wer es ist", "traits" => ["Logical" => 2, "Organized" => 1]],
                    ["emoji" => "😒", "label" => "Ironisch die Tür öffnen", "traits" => ["Social" => 2]],
                    ["emoji" => "🛋️", "label" => "Unter der Decke verstecken", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "HP: Dein Lieblingsort in Hogwarts?",
                "options" => [
                    ["emoji" => "🌙", "label" => "Astronomieturm", "traits" => ["Adventurous" => 2]],
                    ["emoji" => "📚", "label" => "Bibliothek", "traits" => ["Logical" => 3]],
                    ["emoji" => "🌿", "label" => "Gewächshaus", "traits" => ["Romantic" => 2]],
                    ["emoji" => "🔥", "label" => "Gemeinschaftsraum", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "LotR: Im Düsterwald verloren?",
                "options" => [
                    ["emoji" => "🧭", "label" => "Einfach losgehen", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🗺️", "label" => "Karte und Route checken", "traits" => ["Organized" => 3]],
                    ["emoji" => "🌼", "label" => "Pflanzen sammeln", "traits" => ["Creative" => 2]],
                    ["emoji" => "🚶", "label" => "Auf dem Weg bleiben und warten", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "HSM: Gruppenprojekt in der Schule?",
                "options" => [
                    ["emoji" => "🎤", "label" => "Die Präsentation übernehmen", "traits" => ["Social" => 3]],
                    ["emoji" => "🧩", "label" => "Alles koordinieren", "traits" => ["Organized" => 3]],
                    ["emoji" => "🎨", "label" => "Das Design machen", "traits" => ["Creative" => 3]],
                    ["emoji" => "🙈", "label" => "Möglichst wenig sagen", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "Dr. Doofenshmirtz: Dein Urlaub?",
                "options" => [
                    ["emoji" => "🤖", "label" => "Einen Urlaub-Inator erfinden", "traits" => ["Creative" => 3]],
                    ["emoji" => "🏙️", "label" => "Städtetrip mit Plan", "traits" => ["Organized" => 2]],
                    ["emoji" => "🏖️", "label" => "Spontan ans Meer fahren", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🛏️", "label" => "Zuhause bleiben und chillen", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Du gewinnst etwas?",
                "options" => [
                    ["emoji" => "🎉", "label" => "Groß feiern", "traits" => ["Social" => 3]],
                    ["emoji" => "📝", "label" => "Direkt das nächste Ziel festlegen", "traits" => ["Organized" => 2]],
                    ["emoji" => "🤝", "label" => "Mit allen teilen", "traits" => ["Romantic" => 2]],
                    ["emoji" => "🧠", "label" => "Erst mal darüber nachdenken", "traits" => ["Logical" => 2]],
                ]
            ],
            [
                "text" => "HP: Eine Eule als Haustier?",
                "options" => [
                    ["emoji" => "✉️", "label" => "Ja, für Briefe", "traits" => ["Romantic" => 2]],
                    ["emoji" => "📦", "label" => "Nur wenn sie praktisch ist", "traits" => ["Logical" => 2]],
                    ["emoji" => "🦅", "label" => "Auf jeden Fall, Abenteuer!", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🏠", "label" => "Lieber etwas Ruhiges zuhause", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "LotR: Gandalf gibt dir Rat?",
                "options" => [
                    ["emoji" => "⚔️", "label" => "Sofort handeln", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "📋", "label" => "Erst einen Plan machen", "traits" => ["Organized" => 3]],
                    ["emoji" => "❓", "label" => "Noch mehr Infos sammeln", "traits" => ["Logical" => 2]],
                    ["emoji" => "☕", "label" => "Lieber Tee trinken und abwarten", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "Bernd: Wecker klingelt?",
                "options" => [
                    ["emoji" => "😴", "label" => "Snooze drücken", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🧮", "label" => "Den Tag im Kopf durchrechnen", "traits" => ["Logical" => 2]],
                    ["emoji" => "😤", "label" => "Genervt aufstehen", "traits" => ["Spontaneous" => 1]],
                    ["emoji" => "🛌", "label" => "Im Bett bleiben", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "HSM: Nach der Schule?",
                "options" => [
                    ["emoji" => "🎭", "label" => "Proben gehen", "traits" => ["Creative" => 3]],
                    ["emoji" => "📚", "label" => "In einen Club oder Kurs", "traits" => ["Social" => 2, "Organized" => 1]],
                    ["emoji" => "🏠", "label" => "Direkt heim und entspannen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🚗", "label" => "Spontan irgendwohin fahren", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Escape Room?",
                "options" => [
                    ["emoji" => "😂", "label" => "Erst mal einen Witz machen", "traits" => ["Social" => 2]],
                    ["emoji" => "🔍", "label" => "Alle Hinweise logisch analysieren", "traits" => ["Logical" => 3]],
                    ["emoji" => "🛠️", "label" => "Ein Werkzeug basteln", "traits" => ["Creative" => 3]],
                    ["emoji" => "📞", "label" => "Freunde dazuholen", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "HP: Besenflug?",
                "options" => [
                    ["emoji" => "🌀", "label" => "Freestyle und wild", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🛡️", "label" => "Sicher und kontrolliert", "traits" => ["Organized" => 3]],
                    ["emoji" => "🌄", "label" => "Nur zum Aussicht-genießen", "traits" => ["Romantic" => 2]],
                    ["emoji" => "🧹", "label" => "Erst mal üben und verbessern", "traits" => ["Logical" => 2]],
                ]
            ],
            [
                "text" => "LotR: Ein großes Fest?",
                "options" => [
                    ["emoji" => "🗣️", "label" => "Mit allen reden", "traits" => ["Social" => 3]],
                    ["emoji" => "🍷", "label" => "Die Details des Menüs beobachten", "traits" => ["Logical" => 2]],
                    ["emoji" => "🎀", "label" => "Die Deko bewundern", "traits" => ["Creative" => 2]],
                    ["emoji" => "🍽️", "label" => "Eher ruhig essen", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "Bernd: Sommerhitze?",
                "options" => [
                    ["emoji" => "🌳", "label" => "In den Schatten flüchten", "traits" => ["Homebody" => 2]],
                    ["emoji" => "📅", "label" => "Einen kühlen Tagesplan machen", "traits" => ["Organized" => 3]],
                    ["emoji" => "🍦", "label" => "Eis holen und losziehen", "traits" => ["Adventurous" => 2]],
                    ["emoji" => "🏠", "label" => "Vorhänge zu und drin bleiben", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "HSM: Dein größtes Talent?",
                "options" => [
                    ["emoji" => "💃", "label" => "Choreografieren", "traits" => ["Creative" => 3]],
                    ["emoji" => "🗂️", "label" => "Organisieren", "traits" => ["Organized" => 3]],
                    ["emoji" => "🎶", "label" => "Singen", "traits" => ["Romantic" => 2]],
                    ["emoji" => "🤗", "label" => "Andere motivieren", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Dein Zimmer-Schreibtisch?",
                "options" => [
                    ["emoji" => "📦", "label" => "Kreatives Chaos", "traits" => ["Creative" => 2, "Spontaneous" => 2]],
                    ["emoji" => "🏷️", "label" => "Alles beschriftet", "traits" => ["Organized" => 3]],
                    ["emoji" => "🧳", "label" => "Voller Erinnerungsstücke", "traits" => ["Romantic" => 2]],
                    ["emoji" => "🧼", "label" => "Fast leer und minimalistisch", "traits" => ["Logical" => 2]],
                ]
            ],
            [
                "text" => "HP: In der Bibliothek?",
                "options" => [
                    ["emoji" => "📖", "label" => "Nur schnell etwas suchen", "traits" => ["Organized" => 2]],
                    ["emoji" => "🕵️", "label" => "In den verbotenen Bereich schauen", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🤫", "label" => "Ruhig allein lesen", "traits" => ["Homebody" => 2]],
                    ["emoji" => "💬", "label" => "Mit anderen darüber reden", "traits" => ["Social" => 2]],
                ]
            ],
            [
                "text" => "LotR: Deine Rolle in der Gefährten-Gruppe?",
                "options" => [
                    ["emoji" => "🏹", "label" => "Der Scout", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🧭", "label" => "Der Strategist", "traits" => ["Logical" => 3]],
                    ["emoji" => "📚", "label" => "Der Erzähler", "traits" => ["Creative" => 2]],
                    ["emoji" => "🍲", "label" => "Der Versorger", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Serienabend?",
                "options" => [
                    ["emoji" => "👯", "label" => "Mit Freunden schauen", "traits" => ["Social" => 3]],
                    ["emoji" => "📺", "label" => "Die Playlist perfekt vorbereiten", "traits" => ["Organized" => 3]],
                    ["emoji" => "🍿", "label" => "Einfach alles binge-watchen", "traits" => ["Spontaneous" => 2]],
                    ["emoji" => "❌", "label" => "Absagen und schlafen", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Freizeitpark?",
                "options" => [
                    ["emoji" => "🎢", "label" => "Die wildeste Achterbahn", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🧪", "label" => "Erst die Sicherheit checken", "traits" => ["Logical" => 2]],
                    ["emoji" => "🤳", "label" => "Viele Fotos machen", "traits" => ["Social" => 2]],
                    ["emoji" => "🍟", "label" => "Hauptsache Snacks", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "HP: Ein Zaubertrank gelingt nicht?",
                "options" => [
                    ["emoji" => "😅", "label" => "Kurz lachen und neu versuchen", "traits" => ["Spontaneous" => 2]],
                    ["emoji" => "📏", "label" => "Rezept exakt prüfen", "traits" => ["Organized" => 3]],
                    ["emoji" => "🫖", "label" => "Etwas Beruhigendes daraus machen", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🙃", "label" => "Ist halt passiert", "traits" => ["Creative" => 1]],
                ]
            ],
            [
                "text" => "LotR: Der Ring verführt dich?",
                "options" => [
                    ["emoji" => "💍", "label" => "Macht wäre schon cool", "traits" => ["Logical" => 2]],
                    ["emoji" => "🔥", "label" => "Lieber vernichten", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🗃️", "label" => "Sicher verstecken", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🧙", "label" => "Erst jemanden fragen", "traits" => ["Organized" => 2]],
                ]
            ],
            [
                "text" => "HSM: Battle of the Bands?",
                "options" => [
                    ["emoji" => "🌟", "label" => "Vorne auf der Bühne", "traits" => ["Social" => 3]],
                    ["emoji" => "🎼", "label" => "Im Hintergrund die Struktur halten", "traits" => ["Organized" => 3]],
                    ["emoji" => "🎧", "label" => "Einfach zuhören und genießen", "traits" => ["Homebody" => 2]],
                    ["emoji" => "🎸", "label" => "Wild mitmachen", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "Perry the Platypus taucht plötzlich in deinem Versteck auf — was ist dein erster Impuls?",
                "options" => [
                    ["emoji" => "🧪", "label" => "Dramatisch eine Eröffnungsrede halten", "traits" => ["Creative" => 2, "Social" => 1]],
                    ["emoji" => "⚙️", "label" => "Sofort den nächsten -inator aktivieren", "traits" => ["Organized" => 3]],
                    ["emoji" => "🕵️", "label" => "Erst prüfen, ob er wirklich allein ist", "traits" => ["Logical" => 3]],
                    ["emoji" => "☕", "label" => "Ihn ignorieren und erstmal Kaffee holen", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "Dein -inator scheitert bei 99 % — wie reagierst du?",
                "options" => [
                    ["emoji" => "🔁", "label" => "Noch größer, noch verrückter, noch besser", "traits" => ["Spontaneous" => 2, "Creative" => 2]],
                    ["emoji" => "📋", "label" => "Fehleranalyse mit Notizen und Skizzen", "traits" => ["Logical" => 3, "Organized" => 1]],
                    ["emoji" => "😤", "label" => "Kurz explodieren, dann neu anfangen", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "😶", "label" => "Einfach so tun, als wäre es Absicht gewesen", "traits" => ["Social" => 1, "Creative" => 1]],
                ]
            ],
            [
                "text" => "Deine klassische Doofenshmirtz-Backstory?",
                "options" => [
                    ["emoji" => "🪟", "label" => "Von der Familie ständig übersehen worden", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🎻", "label" => "Ein tragischer Moment, der nie erklärt wurde", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🧹", "label" => "Immer der Praktische gewesen, nie der Geliebte", "traits" => ["Logical" => 2, "Homebody" => 1]],
                    ["emoji" => "🎭", "label" => "Ich mache inzwischen lieber Witze daraus", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "Wenn Norm, der Roboter, alles regelt — was denkst du?",
                "options" => [
                    ["emoji" => "🤖", "label" => "Endlich jemand, der zuverlässig ist", "traits" => ["Organized" => 3]],
                    ["emoji" => "🧠", "label" => "Ich will trotzdem alles selbst verstehen", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎉", "label" => "Perfekt, dann kann ich Chaos verursachen", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🛋️", "label" => "Dann kann ich mich endlich zurücklehnen", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Du siehst Perry the Platypus im „unschuldigen Haustier“-Modus — was sagt das über dich?",
                "options" => [
                    ["emoji" => "👀", "label" => "Ich merke sofort, dass da mehr dahintersteckt", "traits" => ["Logical" => 3]],
                    ["emoji" => "😂", "label" => "Ich finde das Ganze einfach genial", "traits" => ["Creative" => 2, "Social" => 1]],
                    ["emoji" => "🧾", "label" => "Ich will die Situation erst logisch einordnen", "traits" => ["Organized" => 2]],
                    ["emoji" => "🤝", "label" => "Ich spiele das Spiel mit und bleibe cool", "traits" => ["Social" => 2]],
                ]
            ],
            [
                "text" => "Doofenshmirtz-Style am Morgen?",
                "options" => [
                    ["emoji" => "☀️", "label" => "Erst reden, dann existieren", "traits" => ["Social" => 1, "Logical" => 1]],
                    ["emoji" => "🧪", "label" => "Sofort an einem Projekt basteln", "traits" => ["Creative" => 3]],
                    ["emoji" => "📅", "label" => "Den Tag komplett durchstrukturieren", "traits" => ["Organized" => 3]],
                    ["emoji" => "💤", "label" => "Ich brauche ein emotionales Aufwärmen", "traits" => ["Homebody" => 2, "Romantic" => 1]],
                ]
            ],
            [
                "text" => "Wenn jemand deinen Plan für lächerlich hält, dann…",
                "options" => [
                    ["emoji" => "🧪", "label" => "Ich werde nur noch entschlossener", "traits" => ["Spontaneous" => 2]],
                    ["emoji" => "🧊", "label" => "Ich merke mir das sehr genau", "traits" => ["Organized" => 2]],
                    ["emoji" => "😅", "label" => "Ich mache einen Witz daraus", "traits" => ["Creative" => 3]],
                    ["emoji" => "🧠", "label" => "Ich prüfe, ob die Kritik berechtigt ist", "traits" => ["Logical" => 3]],
                ]
            ],
            [
                "text" => "Wenn dein Leben ein Doofenshmirtz-Tag wäre, was wäre dein wichtigster Plot-Twist?",
                "options" => [
                    ["emoji" => "🪄", "label" => "Ich mache aus Peinlichkeit etwas Charmantes", "traits" => ["Creative" => 2, "Romantic" => 1]],
                    ["emoji" => "🔧", "label" => "Ich repariere das Problem selbst", "traits" => ["Organized" => 3]],
                    ["emoji" => "🎬", "label" => "Ich inszeniere daraus eine bessere Story", "traits" => ["Social" => 1, "Creative" => 2]],
                    ["emoji" => "🏠", "label" => "Ich ziehe mich kurz zurück und komme stärker wieder", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Was ist dein Sommer-Look?",
                "options" => [
                    ["emoji" => "👕", "label" => "Gestreiftes Shirt & Shorts", "traits" => ["Creative" => 2, "Spontaneous" => 1]],
                    ["emoji" => "👗", "label" => "Immer top-gestylt und schick", "traits" => ["Organized" => 2, "Social" => 1]],
                    ["emoji" => "🕵️", "label" => "Unauffällig in Tarnfarben", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🥼", "label" => "Klassischer Labor-Kittel", "traits" => ["Logical" => 2, "Creative" => 1]],
                ]
            ],
            [
                "text" => "HP: Welches magische Item wertet dein Outfit auf?",
                "options" => [
                    ["emoji" => "🧣", "label" => "Ein Schal in Hausfarben", "traits" => ["Romantic" => 2, "Homebody" => 1]],
                    ["emoji" => "🧥", "label" => "Ein edler Umhang", "traits" => ["Organized" => 2]],
                    ["emoji" => "🕶️", "label" => "Eine verrückte Brille (Luna-Style)", "traits" => ["Creative" => 3]],
                    ["emoji" => "⚡", "label" => "Einfach meine markante Narbe", "traits" => ["Adventurous" => 2]],
                ]
            ],
            [
                "text" => "LotR: Dein Look in Mittelerde?",
                "options" => [
                    ["emoji" => "🧝", "label" => "Edles Elben-Gewand", "traits" => ["Romantic" => 2, "Creative" => 1]],
                    ["emoji" => "⛺", "label" => "Robuste Waldläufer-Rüstung", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🦶", "label" => "Geflickte Weste und barfuß", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🛡️", "label" => "Schwere Zwergen-Rüstung", "traits" => ["Logical" => 1, "Organized" => 2]],
                ]
            ],
            [
                "text" => "Bernd: Dein modisches Statement?",
                "options" => [
                    ["emoji" => "😴", "label" => "Ein grauer Schlafanzug", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🍞", "label" => "Formstabile Kastenbrot-Silhouette", "traits" => ["Organized" => 2]],
                    ["emoji" => "👕", "label" => "Ein ganz einfaches T-Shirt", "traits" => ["Logical" => 2]],
                    ["emoji" => "❌", "label" => "Gar keines. Warum auch?", "traits" => ["Homebody" => 2]],
                ]
            ],
            [
                "text" => "HSM: Dein Look für den Schulflur?",
                "options" => [
                    ["emoji" => "✨", "label" => "Glitzer und Glamour", "traits" => ["Social" => 3]],
                    ["emoji" => "👟", "label" => "Bequeme Tanzklamotten", "traits" => ["Creative" => 2, "Spontaneous" => 1]],
                    ["emoji" => "🏀", "label" => "Klassisches Team-Jersey", "traits" => ["Social" => 2]],
                    ["emoji" => "📖", "label" => "Dezent mit Laptop & Brille", "traits" => ["Logical" => 3]],
                ]
            ],
        ];

        $partnerQuestions = [
            [
                "text" => "Welche Magie im Alltag?",
                "options" => [
                    ["emoji" => "✨", "label" => "Ordnung per Zauberstab", "traits" => ["Organized" => 3]],
                    ["emoji" => "🥘", "label" => "Töpfe rühren von selbst", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🕊️", "label" => "Nachrichten per Eule", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🚪", "label" => "Apparieren (Teleport)", "traits" => ["Adventurous" => 3]],
                ]
            ],
            [
                "text" => "Sidekick-Energie?",
                "options" => [
                    ["emoji" => "🐾", "label" => "Balous (Gemütlichkeit)", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🦾", "label" => "Jarvis (Effizienz)", "traits" => ["Logical" => 3]],
                    ["emoji" => "🧞", "label" => "Dschinni (Chaos/Spaß)", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🕯️", "label" => "Lumière (Charme)", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "Date: Drachenreiten?",
                "options" => [
                    ["emoji" => "🐉", "label" => "\"Klar, fliegen wir!\"", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🛡️", "label" => "\"Ist das sicher?\"", "traits" => ["Logical" => 3]],
                    ["emoji" => "📸", "label" => "\"Lächeln für das Foto!\"", "traits" => ["Social" => 3]],
                    ["emoji" => "🧺", "label" => "\"Können wir lieber picknicken?\"", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "HP: Weihnachtsball-Partner?",
                "options" => [
                    ["emoji" => "💃", "label" => "Tanzt die ganze Nacht", "traits" => ["Social" => 3]],
                    ["emoji" => "🥂", "label" => "Redet tiefgründig am Rand", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🥗", "label" => "Genießt nur das Buffet", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🎩", "label" => "Hat das beste Kostüm", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "LotR: Dein Gefährte?",
                "options" => [
                    ["emoji" => "🏹", "label" => "Kühl & Treffsicher", "traits" => ["Organized" => 3]],
                    ["emoji" => "🧔", "label" => "Grob & Herzlich", "traits" => ["Social" => 3]],
                    ["emoji" => "📜", "label" => "Weise & Geheimnisvoll", "traits" => ["Logical" => 3]],
                    ["emoji" => "🍲", "label" => "Sorgsam & Loyal", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "HSM: Karaoke-Song?",
                "options" => [
                    ["emoji" => "🎤", "label" => "Disney-Klassiker", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🎸", "label" => "Punk-Rock Hymne", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🎹", "label" => "Gefühlvolle Eigenkreation", "traits" => ["Creative" => 3]],
                    ["emoji" => "💃", "label" => "80s Dance-Hit", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Idealer Urlaub?",
                "options" => [
                    ["emoji" => "🌧️", "label" => "Regenwoche in England", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🏔️", "label" => "Einsame Berghütte", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🏢", "label" => "Städtetrip mit Guide", "traits" => ["Organized" => 3]],
                    ["emoji" => "🏖️", "label" => "All-Inclusive Resort", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "HP: Hauself-Haltung?",
                "options" => [
                    ["emoji" => "🧤", "label" => "Freiheit für alle!", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "☕", "label" => "\"Ein Tee wäre jetzt gut\"", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🧹", "label" => "Ordnung ist wichtig", "traits" => ["Organized" => 3]],
                    ["emoji" => "🤝", "label" => "Wir arbeiten zusammen", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Das perfekte Gadget?",
                "options" => [
                    ["emoji" => "🚀", "label" => "Jetpack (Schnelligkeit)", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🕶️", "label" => "Röntgenbrille (Neugier)", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎸", "label" => "Hologramm-Gitarre", "traits" => ["Creative" => 3]],
                    ["emoji" => "🤖", "label" => "Koch-Roboter", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "LotR: Geschenk von Galadriel?",
                "options" => [
                    ["emoji" => "💡", "label" => "Ein Licht (Hoffnung)", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🗡️", "label" => "Ein Dolch (Schutz)", "traits" => ["Organized" => 3]],
                    ["emoji" => "🧥", "label" => "Ein Tarnmantel (Ruhe)", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🌳", "label" => "Ein Samen (Zukunft)", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "HSM: Basketball-Spiel?",
                "options" => [
                    ["emoji" => "📣", "label" => "Anfeuern in der ersten Reihe", "traits" => ["Social" => 3]],
                    ["emoji" => "🏀", "label" => "Selbst mitspielen", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🌭", "label" => "Nur wegen der Snacks da", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🎺", "label" => "In der Marching Band", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "HP: In der Nokturngasse?",
                "options" => [
                    ["emoji" => "🔦", "label" => "Mutig erkunden", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🏃‍♂️", "label" => "Schnell wieder raus", "traits" => ["Logical" => 3]],
                    ["emoji" => "🕯️", "label" => "Die Ästhetik bewundern", "traits" => ["Creative" => 3]],
                    ["emoji" => "📜", "label" => "Ein seltenes Buch suchen", "traits" => ["Organized" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Das perfekte Haustier?",
                "options" => [
                    ["emoji" => "🐈", "label" => "Eine faule Katze", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🐕", "label" => "Ein aktiver Hund", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🐢", "label" => "Eine Schildkröte", "traits" => ["Logical" => 3]],
                    ["emoji" => "🪵", "label" => "Ein Stein", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Party-Motto?",
                "options" => [
                    ["emoji" => "🌊", "label" => "Unterwasser-Welt", "traits" => ["Creative" => 3]],
                    ["emoji" => "👾", "label" => "Retro-Gaming", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎭", "label" => "Maskenball", "traits" => ["Social" => 3]],
                    ["emoji" => "🏰", "label" => "Mittelalter", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "HP: Karte des Rumtreibers?",
                "options" => [
                    ["emoji" => "🤫", "label" => "Unfuch anstellen", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🕵️‍♂️", "label" => "Leute beobachten", "traits" => ["Logical" => 3]],
                    ["emoji" => "🚪", "label" => "Geheimgänge finden", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🧹", "label" => "Den kürzesten Weg suchen", "traits" => ["Organized" => 3]],
                ]
            ],
            [
                "text" => "LotR: Ein Fest bei Bilbo?",
                "options" => [
                    ["emoji" => "💃", "label" => "Tanzen auf den Tischen", "traits" => ["Social" => 3]],
                    ["emoji" => "🍷", "label" => "Ein guter Wein am Kamin", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🎆", "label" => "Das Feuerwerk bewundern", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "👂", "label" => "Den Geschichten zuhören", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "HSM: Das erste Auto?",
                "options" => [
                    ["emoji" => "🚗", "label" => "Ein schicker Sportwagen", "traits" => ["Social" => 3]],
                    ["emoji" => "🚐", "label" => "Ein Camper für Roadtrips", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🚲", "label" => "Ein Fahrrad (Umwelt!)", "traits" => ["Logical" => 3]],
                    ["emoji" => "🚙", "label" => "Ein verlässlicher SUV", "traits" => ["Organized" => 3]],
                ]
            ],
            [
                "text" => "HP: Dein stärkster Zauberspruch?",
                "options" => [
                    ["emoji" => "🪄", "label" => "Accio (Bequemlichkeit)", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🛡️", "label" => "Protego (Schutz)", "traits" => ["Organized" => 3]],
                    ["emoji" => "💡", "label" => "Lumos (Klarheit)", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎆", "label" => "Expelliarmus (Fairness)", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Streit-Kultur?",
                "options" => [
                    ["emoji" => "🗣️", "label" => "Ausdiskutieren", "traits" => ["Social" => 3]],
                    ["emoji" => "🛠️", "label" => "Etwas bauen, um es zu lösen", "traits" => ["Logical" => 3]],
                    ["emoji" => "🤐", "label" => "Erstmal schmollen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🎶", "label" => "Einen Song darüber singen", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "LotR: Die Minen von Moria?",
                "options" => [
                    ["emoji" => "⚔️", "label" => "\"Ich gehe voran!\"", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🕯️", "label" => "\"Bleibt zusammen!\"", "traits" => ["Organized" => 3]],
                    ["emoji" => "📜", "label" => "\"Was steht an der Tür?\"", "traits" => ["Logical" => 3]],
                    ["emoji" => "🏃‍♂️", "label" => "\"Lauft, ihr Narren!\"", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Ein Kompliment?",
                "options" => [
                    ["emoji" => "😊", "label" => "\"Danke!\" (verlegen)", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🤨", "label" => "\"Was willst du von mir?\"", "traits" => ["Logical" => 3]],
                    ["emoji" => "🍞", "label" => "\"Mist.\"", "traits" => ["Homebody" => 3]],
                    ["emoji" => "😎", "label" => "\"Ich weiß.\"", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "HP: Der Drei-Besen-Drink?",
                "options" => [
                    ["emoji" => "🍺", "label" => "Butterbier (Klassisch)", "traits" => ["Social" => 3]],
                    ["emoji" => "🥃", "label" => "Feuerwhisky (Stark)", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🧃", "label" => "Kürbissaft (Sanft)", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🧪", "label" => "Ein bunter Mix", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "HSM: Talent-Show Jury?",
                "options" => [
                    ["emoji" => "⚖️", "label" => "Streng & Fair", "traits" => ["Organized" => 3]],
                    ["emoji" => "❤️", "label" => "Mitfühlend & Motivierend", "traits" => ["Romantic" => 3]],
                    ["emoji" => "✨", "label" => "Fokus auf Showmanship", "traits" => ["Social" => 3]],
                    ["emoji" => "📝", "label" => "Analytisch & Detailverliebt", "traits" => ["Logical" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Wenn alles vorbei ist?",
                "options" => [
                    ["emoji" => "🌅", "label" => "Den Sonnenuntergang genießen", "traits" => ["Romantic" => 3]],
                    ["emoji" => "📝", "label" => "Das nächste Projekt planen", "traits" => ["Organized" => 3]],
                    ["emoji" => "🍕", "label" => "Pizza für alle bestellen", "traits" => ["Social" => 3]],
                    ["emoji" => "🛌", "label" => "Sofort einschlafen", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Abschluss: Der Vibe Check?",
                "options" => [
                    ["emoji" => "🎡", "label" => "Bunt, Laut, Spontan", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "📚", "label" => "Tiefgründig, Ruhig, Stabil", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎭", "label" => "Kreativ, Dramatisch, Schön", "traits" => ["Creative" => 3]],
                    ["emoji" => "🏠", "label" => "Gemütlich, Ehrlich, Echt", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Welche Magie im Alltag sollte dein Match haben?",
                "options" => [
                    ["emoji" => "✨", "label" => "Alles ordentlich verzaubern", "traits" => ["Organized" => 3]],
                    ["emoji" => "🍲", "label" => "Das Essen wie von selbst machen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "💌", "label" => "Süße Nachrichten schicken", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🚪", "label" => "Spontan überallhin apparieren", "traits" => ["Adventurous" => 3]],
                ]
            ],
            [
                "text" => "Dein idealer Sidekick?",
                "options" => [
                    ["emoji" => "😎", "label" => "Witzig und gesellig", "traits" => ["Social" => 3]],
                    ["emoji" => "🧠", "label" => "Ruhig und logisch", "traits" => ["Logical" => 3]],
                    ["emoji" => "🛋️", "label" => "Gemütlich und entspannt", "traits" => ["Homebody" => 3]],
                    ["emoji" => "⚡", "label" => "Laut, wild und spontan", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "Perfektes Date?",
                "options" => [
                    ["emoji" => "🥾", "label" => "Wandern und Natur", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🖼️", "label" => "Museum und Kaffee", "traits" => ["Creative" => 3]],
                    ["emoji" => "🎲", "label" => "Brettspielabend zuhause", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🚗", "label" => "Spontaner Roadtrip", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "HP: Quidditch-Teamkollege?",
                "options" => [
                    ["emoji" => "📣", "label" => "Motiviert alle lautstark", "traits" => ["Social" => 3]],
                    ["emoji" => "🎯", "label" => "Immer präzise", "traits" => ["Organized" => 3]],
                    ["emoji" => "😄", "label" => "Macht das Spiel leicht", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🤝", "label" => "Hält das Team zusammen", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "LotR: Dein Reisepartner?",
                "options" => [
                    ["emoji" => "🗡️", "label" => "Mutig und furchtlos", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🧭", "label" => "Plant alles vorab", "traits" => ["Organized" => 3]],
                    ["emoji" => "✒️", "label" => "Erzählt schöne Geschichten", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🔥", "label" => "Liebt ein warmes Zuhause", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "HSM: Dein perfekter Duett-Partner?",
                "options" => [
                    ["emoji" => "🎤", "label" => "Kann die Menge mitreißen", "traits" => ["Social" => 3]],
                    ["emoji" => "🎼", "label" => "Achtet auf Harmonie", "traits" => ["Creative" => 3]],
                    ["emoji" => "🗂️", "label" => "Probt alles genau", "traits" => ["Organized" => 3]],
                    ["emoji" => "💖", "label" => "Singt mit Gefühl", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Wochenend-Plan mit deinem Match?",
                "options" => [
                    ["emoji" => "🛋️", "label" => "Nichts tun und entspannen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🧾", "label" => "Alles fein durchplanen", "traits" => ["Organized" => 3]],
                    ["emoji" => "🚶", "label" => "Einen langen Spaziergang machen", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🍝", "label" => "Gemeinsam kochen und essen", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Wer soll beim Gadget helfen?",
                "options" => [
                    ["emoji" => "💡", "label" => "Mit neuen Ideen glänzen", "traits" => ["Creative" => 3]],
                    ["emoji" => "🔧", "label" => "Praktisch und effizient bauen", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎨", "label" => "Das Ganze hübsch machen", "traits" => ["Creative" => 3]],
                    ["emoji" => "🧪", "label" => "Das Experiment testen", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "HP: Beim Yule Ball?",
                "options" => [
                    ["emoji" => "💃", "label" => "Tanzt die ganze Nacht", "traits" => ["Social" => 3]],
                    ["emoji" => "💬", "label" => "Führt tiefe Gespräche", "traits" => ["Romantic" => 3]],
                    ["emoji" => "👀", "label" => "Beobachtet alles genau", "traits" => ["Logical" => 3]],
                    ["emoji" => "🧣", "label" => "Bleibt lieber gemütlich am Rand", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "LotR: Welches Geschenk vom Match?",
                "options" => [
                    ["emoji" => "🗺️", "label" => "Eine Reisekarte", "traits" => ["Organized" => 3]],
                    ["emoji" => "🌹", "label" => "Blumen oder etwas Romantisches", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🎒", "label" => "Etwas für das nächste Abenteuer", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🖐️", "label" => "Etwas Handgemachtes", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "HSM: Wie soll dein Match Konflikte lösen?",
                "options" => [
                    ["emoji" => "🗣️", "label" => "Direkt und offen reden", "traits" => ["Social" => 3]],
                    ["emoji" => "📋", "label" => "Erst einen Plan machen", "traits" => ["Organized" => 3]],
                    ["emoji" => "🧊", "label" => "Erstmal Abstand brauchen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🎢", "label" => "Spontan darauf reagieren", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Messaging-Style deines Matches?",
                "options" => [
                    ["emoji" => "😂", "label" => "Viele Memes und Sprachnachrichten", "traits" => ["Social" => 3]],
                    ["emoji" => "✍️", "label" => "Kurz, klar und logisch", "traits" => ["Logical" => 3]],
                    ["emoji" => "💌", "label" => "Warm und liebevoll", "traits" => ["Romantic" => 3]],
                    ["emoji" => "💤", "label" => "Nur ab und zu melden", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Der perfekte Sonntag mit deinem Match?",
                "options" => [
                    ["emoji" => "🎡", "label" => "Etwas Neues erleben", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🧹", "label" => "Zuhause zusammen was schaffen", "traits" => ["Organized" => 3]],
                    ["emoji" => "🍕", "label" => "Auf dem Sofa essen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🎨", "label" => "Gemeinsam etwas basteln", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "HP: Welche Haus-Energie soll dein Match haben?",
                "options" => [
                    ["emoji" => "🦁", "label" => "Mutig und mutmachend", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🦅", "label" => "Klug und reflektiert", "traits" => ["Logical" => 3]],
                    ["emoji" => "🦡", "label" => "Warm und loyal", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🐍", "label" => "Ambitioniert und strukturiert", "traits" => ["Organized" => 3]],
                ]
            ],
            [
                "text" => "LotR: Welche Reisehaltung soll dein Match haben?",
                "options" => [
                    ["emoji" => "🧭", "label" => "Immer auf Entdeckung aus", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🗂️", "label" => "Gut vorbereitet und planvoll", "traits" => ["Organized" => 3]],
                    ["emoji" => "🕯️", "label" => "Lieber ruhig und gemütlich", "traits" => ["Homebody" => 3]],
                    ["emoji" => "✨", "label" => "Offen für Überraschungen", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "HSM: Dein Karaoke-Match?",
                "options" => [
                    ["emoji" => "🎤", "label" => "Bühnenmensch mit Ausstrahlung", "traits" => ["Social" => 3]],
                    ["emoji" => "✍️", "label" => "Schreibt eigene Songs", "traits" => ["Creative" => 3]],
                    ["emoji" => "😳", "label" => "Etwas schüchtern, aber süß", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🕺", "label" => "Bringt die besten Moves mit", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Regen-Date?",
                "options" => [
                    ["emoji" => "🛋️", "label" => "Decke, Tee, Sofa", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🌧️", "label" => "Trotzdem rausgehen", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "📚", "label" => "In ein Café mit Buch", "traits" => ["Logical" => 3]],
                    ["emoji" => "🍳", "label" => "Zusammen kochen", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Dein Traum-Urlaubspartner?",
                "options" => [
                    ["emoji" => "🏕️", "label" => "Immer für Abenteuer zu haben", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "📅", "label" => "Plant jede Etappe", "traits" => ["Organized" => 3]],
                    ["emoji" => "😌", "label" => "Will auch mal einfach ruhen", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🎒", "label" => "Bringt originelle Ideen mit", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "HP: Welche magische Tier-Energie im Match?",
                "options" => [
                    ["emoji" => "🦉", "label" => "Treu und nachdenklich", "traits" => ["Romantic" => 3]],
                    ["emoji" => "🐈", "label" => "Gemütlich und unabhängig", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🐸", "label" => "Eher skurril und unaufgeregt", "traits" => ["Logical" => 3]],
                    ["emoji" => "🦊", "label" => "Schlau und verspielt", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "LotR: Dein Battle-Buddy?",
                "options" => [
                    ["emoji" => "🛡️", "label" => "Verlässlich und organisiert", "traits" => ["Organized" => 3]],
                    ["emoji" => "⚔️", "label" => "Mutig und direkt", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "💛", "label" => "Fürsorglich und warm", "traits" => ["Romantic" => 3]],
                    ["emoji" => "😄", "label" => "Locker und gesellig", "traits" => ["Social" => 3]],
                ]
            ],
            [
                "text" => "HSM: Social-Media-Vibe deines Matches?",
                "options" => [
                    ["emoji" => "🎨", "label" => "Ästhetisch und kreativ", "traits" => ["Creative" => 3]],
                    ["emoji" => "🧺", "label" => "Cozy und homey", "traits" => ["Homebody" => 3]],
                    ["emoji" => "📌", "label" => "Sauber kuratiert", "traits" => ["Organized" => 3]],
                    ["emoji" => "🎉", "label" => "Wild und spontan", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "Bernd: Welches Kompliment soll dein Match geben?",
                "options" => [
                    ["emoji" => "🧠", "label" => "Schlau und durchdacht", "traits" => ["Logical" => 3]],
                    ["emoji" => "💖", "label" => "Warm und ehrlich", "traits" => ["Romantic" => 3]],
                    ["emoji" => "😄", "label" => "Lustig und locker", "traits" => ["Social" => 3]],
                    ["emoji" => "🏠", "label" => "Ruhig und vertraut", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Wie soll dein Match Probleme lösen?",
                "options" => [
                    ["emoji" => "🛠️", "label" => "Kreativ etwas Neues bauen", "traits" => ["Creative" => 3]],
                    ["emoji" => "📐", "label" => "Mit Logik vorgehen", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎲", "label" => "Spontan improvisieren", "traits" => ["Spontaneous" => 3]],
                    ["emoji" => "🤗", "label" => "Erst mal beruhigen", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "HP: Welche Zauber-Energie soll dein Match haben?",
                "options" => [
                    ["emoji" => "🪄", "label" => "Bequem und praktisch", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🛡️", "label" => "Schützend und zuverlässig", "traits" => ["Organized" => 3]],
                    ["emoji" => "💡", "label" => "Klar und reflektiert", "traits" => ["Logical" => 3]],
                    ["emoji" => "🔑", "label" => "Offen für Neues", "traits" => ["Adventurous" => 3]],
                ]
            ],
            [
                "text" => "Abschluss: Dein ideales Match-Vibe?",
                "options" => [
                    ["emoji" => "⚡", "label" => "Laut, lebendig, sozial", "traits" => ["Social" => 3]],
                    ["emoji" => "🏠", "label" => "Ruhig, sicher, gemütlich", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🧠", "label" => "Tiefgründig, klug, klar", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎢", "label" => "Wild, kreativ, spontan", "traits" => ["Creative" => 3]],
                ]
            ],
            [
                "text" => "Dein perfekter „Evil Plan“-Partner wäre jemand, der…",
                "options" => [
                    ["emoji" => "🧠", "label" => "deine Ideen kritisch mitdenkt", "traits" => ["Logical" => 3]],
                    ["emoji" => "🎨", "label" => "noch verrücktere Ideen beisteuert", "traits" => ["Creative" => 3]],
                    ["emoji" => "🛠️", "label" => "alles sauber organisiert", "traits" => ["Organized" => 3]],
                    ["emoji" => "❤️", "label" => "dich auch bei peinlichen Momenten ernst nimmt", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "Was ist für dich romantischer: bei Doofenshmirtz zu leben oder bei ihm zu daten?",
                "options" => [
                    ["emoji" => "🏭", "label" => "Ein chaotisches Labor mit Herz", "traits" => ["Creative" => 2, "Romantic" => 1]],
                    ["emoji" => "📋", "label" => "Ein Partner, der deinen Wahnsinn strukturiert", "traits" => ["Organized" => 3]],
                    ["emoji" => "🎭", "label" => "Jemand, der deine Dramatik feiert", "traits" => ["Social" => 2]],
                    ["emoji" => "🛏️", "label" => "Jemand, der Ruhe nach dem Chaos bringt", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "Phineas: Wie soll dein Match gestylt sein?",
                "options" => [
                    ["emoji" => "👔", "label" => "Elegant und strukturiert", "traits" => ["Organized" => 3]],
                    ["emoji" => "🎨", "label" => "Bunt und künstlerisch", "traits" => ["Creative" => 3]],
                    ["emoji" => "🥾", "label" => "Praktisch für Abenteuer", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "👕", "label" => "Locker und entspannt", "traits" => ["Homebody" => 3]],
                ]
            ],
            [
                "text" => "HP: Der Kleidungsstil deines Matches in der Zauberwelt?",
                "options" => [
                    ["emoji" => "🎩", "label" => "Traditionell und förmlich", "traits" => ["Organized" => 3]],
                    ["emoji" => "🧹", "label" => "Sportlich und aktiv", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🌙", "label" => "Verträumt und individuell", "traits" => ["Romantic" => 3]],
                    ["emoji" => "📜", "label" => "Schlicht und funktional", "traits" => ["Logical" => 3]],
                ]
            ],
            [
                "text" => "LotR: Die Ausstrahlung deines idealen Reisebegleiters?",
                "options" => [
                    ["emoji" => "🛡️", "label" => "Majestätisch und stolz", "traits" => ["Organized" => 3]],
                    ["emoji" => "🍂", "label" => "Naturverbunden und geheimnisvoll", "traits" => ["Adventurous" => 3]],
                    ["emoji" => "🔥", "label" => "Herzlich und bodenständig", "traits" => ["Homebody" => 3]],
                    ["emoji" => "✨", "label" => "Ätherisch und zeitlos", "traits" => ["Romantic" => 3]],
                ]
            ],
            [
                "text" => "Bernd das Brot: Was trägt dein Match am liebsten?",
                "options" => [
                    ["emoji" => "🧥", "label" => "Einen klassischen Regenmantel", "traits" => ["Logical" => 3]],
                    ["emoji" => "🧶", "label" => "Selbstgestrickte Wollpullis", "traits" => ["Homebody" => 3]],
                    ["emoji" => "🏷️", "label" => "Markenkleidung und Trends", "traits" => ["Social" => 3]],
                    ["emoji" => "🧣", "label" => "Etwas Farbenfrohes", "traits" => ["Spontaneous" => 3]],
                ]
            ],
            [
                "text" => "HSM: Der Style deines Duett-Partners?",
                "options" => [
                    ["emoji" => "💎", "label" => "Auffällig und luxuriös", "traits" => ["Social" => 3]],
                    ["emoji" => "🎧", "label" => "Cool und modern", "traits" => ["Creative" => 3]],
                    ["emoji" => "🧺", "label" => "Süß und natürlich", "traits" => ["Romantic" => 3]],
                    ["emoji" => "📝", "label" => "Schick und seriös", "traits" => ["Organized" => 3]],
                ]
            ],
        ];

        foreach ($characterQuestions as $q) {
            $text = $q['text'];
            $universe = null;

            // Detect and Clean Prefixes
            $prefixes = [
                'Phineas & Ferb: ' => 'Phineas & Ferb',
                'Bernd das Brot: ' => 'Bernd das Brot',
                'HP: ' => 'Harry Potter',
                'HSM: ' => 'High School Musical',
                'LotR: ' => 'Lord of the Rings',
                'Dr. Doofenshmirtz: ' => 'Phineas & Ferb',
                'Bernd: ' => 'Bernd das Brot',
                'Phineas: ' => 'Phineas & Ferb',
                'Abschluss: ' => 'Phineas & Ferb', // Falling back to P&F for general closers
            ];

            foreach ($prefixes as $prefix => $uni) {
                if (str_starts_with($text, $prefix)) {
                    $universe = $uni;
                    $text = str_replace($prefix, '', $text);
                    break;
                }
            }

            // Fallbacks for those without prefixes or Doof special questions
            if (!$universe) {
                if (str_contains($text, 'Doofenshmirtz') || str_contains($text, 'Perry') || str_contains($text, '-inator') || str_contains($text, 'Norm, der Roboter') || str_contains($text, 'Plan')) {
                    $universe = 'Phineas & Ferb';
                } elseif (str_contains($text, 'Hogwarts') || str_contains($text, 'Zaubertrank')) {
                    $universe = 'Harry Potter';
                }
            }

            DatingQuestion::create([
                'text' => trim($text),
                'type' => 'character',
                'universe' => $universe,
                'options' => $q['options'],
            ]);
        }

        foreach ($partnerQuestions as $q) {
            $text = $q['text'];
            $universe = null;

            $prefixes = [
                'Phineas & Ferb: ' => 'Phineas & Ferb',
                'Bernd das Brot: ' => 'Bernd das Brot',
                'HP: ' => 'Harry Potter',
                'HSM: ' => 'High School Musical',
                'LotR: ' => 'Lord of the Rings',
                'Dr. Doofenshmirtz: ' => 'Phineas & Ferb',
                'Bernd: ' => 'Bernd das Brot',
                'Phineas: ' => 'Phineas & Ferb',
                'Abschluss: ' => 'Phineas & Ferb',
            ];

            foreach ($prefixes as $prefix => $uni) {
                if (str_starts_with($text, $prefix)) {
                    $universe = $uni;
                    $text = str_replace($prefix, '', $text);
                    break;
                }
            }

            // Fallbacks for partner questions
            if (!$universe) {
                if (str_contains($text, 'Magie') || str_contains($text, 'Eule') || str_contains($text, 'Hogwarts') || str_contains($text, 'Hauself')) {
                    $universe = 'Harry Potter';
                } elseif (str_contains($text, 'Drachenreiten') || str_contains($text, 'Moria') || str_contains($text, 'Gefährte') || str_contains($text, 'Galadriel')) {
                    $universe = 'Lord of the Rings';
                } elseif (str_contains($text, 'Doofenshmirtz') || str_contains($text, 'Perry') || str_contains($text, 'inator') || str_contains($text, 'Evil Plan') || str_contains($text, 'Sidekick')) {
                    $universe = 'Phineas & Ferb';
                } elseif (str_contains($text, 'Karaoke') || str_contains($text, 'Basketball') || str_contains($text, 'Social-Media-Vibe')) {
                    $universe = 'High School Musical';
                } elseif (str_contains($text, 'Date') || str_contains($text, 'Vibe Check')) {
                    $universe = 'Phineas & Ferb';
                }
            }

            DatingQuestion::create([
                'text' => trim($text),
                'type' => 'partner',
                'universe' => $universe,
                'options' => $q['options'],
            ]);
        }
    }
}
