# Dating Matcher - Funktionsübersicht

## 1. Top-Down Übersicht
Der **Dating Matcher** ist ein interaktives Tool zur spielerischen Analyse der eigenen Persönlichkeit und der Partner-Präferenzen. Es ist als mehrstufiger Wizard (Single Page Applikation) aufgebaut, der Nutzerdaten sammelt, Trait-Scores berechnet und eine visuelle sowie textuelle Auswertung generiert.

## 2. Kernfunktionen & Workflow
*   **Schritt 1: Personalisierung:** Erfassung von Basis-Daten (Name, Geschlecht, Suchpräferenz).
*   **Schritt 2: Selbst-Analyse (User Traits):** 6-8 Multiple-Choice-Fragen zu Lebensgewohnheiten und Charakter (z. B. Stressreaktion, Humor, Freitagabend-Planung).
*   **Schritt 3: Wunsch-Profil (Partner Traits):** Fragen zum idealen Partner, Red Flags und attraktivsten Merkmalen (inkl. visueller Auswahl von Promis/Charakteren).
*   **Schritt 4: Das "Verdict" (Ergebnis):**
    *   **Vibe Matrix:** Ein grafisches **Radar-Diagramm (Spider Chart)** visualisiert die Trait-Verteilung des Nutzers.
    *   **Stärken & Schwächen:** Dynamische Texte basierend auf dem höchsten und niedrigsten Score.
    *   **Perfect Match Summary:** Eine Kurzbeschreibung des idealen Partners basierend auf den gewählten Präferenzen.

## 3. Das Trait-System (Metriken)
Die Logik bewertet Nutzer und Wunschpartner in 8 Dimensionen:
1.  **Spontaneous:** Spontaneität & Energie.
2.  **Homebody:** Fokus auf Gemütlichkeit & Zuhause.
3.  **Adventurous:** Abenteuerlust & Neugier.
4.  **Romantic:** Emotionalität & Tiefgang.
5.  **Logical:** Rationalität & Verlässlichkeit.
6.  **Organized:** Struktur & Planung.
7.  **Social:** Geselligkeit & Extrovertiertheit.
8.  **Creative:** Kreativität & Ästhetik.

## 4. Technische Implementierung
*   **Architektur:** Laravel (Backend) + Alpine.js (Frontend-Logik).
*   **Frontend-State:** Alle Berechnungen und Zustandsübergänge erfolgen reaktiv im Frontend.
*   **Visualisierung:** Integration von **Chart.js** für das dynamische Radar-Diagramm.
*   **Styling:** Tailwind CSS mit Fokus auf moderne UI-Elemente (Gradients, Glasmorphismus, Animationen).

## 5. Zielsetzung & UX
Das Tool dient der Gamification und Nutzerinteraktion. Durch humorvolle Fragen (z. B. "High School Musical"-Bezüge) und eine visuell ansprechende Auswertung wird eine hohe Shareability und Nutzerbindung angestrebt.

---

## 6. Guide: Neue Fragen hinzufügen
Wenn du neue Fragen für den Dating Matcher konzipierst, müssen diese bestimmten Rahmenbedingungen folgen, damit das mathematische und visuelle Endergebnis konsistent bleibt.

### Anforderungen an den Content
1.  **Struktur:** Eine Frage besteht immer aus einem Fragetext und (idealerweise) **genau 4 Antwortmöglichkeiten**, um die UI-Balance (Bilder-Grid) zu wahren.
2.  **Visuals:** Jede Antwortoption benötigt entweder ein **Emoji** oder eine **Bild-URL** (z. B. für Personen-Visualisierungen), um das Design lebendig zu halten.
3.  **Texte:**
    *   **Label:** Ein kurzes Schlagwort (z. B. "French Fries").
    *   **Description:** Ein humorvoller, erklärender Nebensatz (z. B. "Classic, loved by everyone").

### Die Logik-Dimensionen (Traits)
Jede Antwortmöglichkeit **muss** einen Impact auf die "Vibe Matrix" haben. Du musst entscheiden, welche der 8 Traits durch eine Antwort gestärkt werden:
*   `Spontaneous`, `Homebody`, `Adventurous`, `Romantic`, `Logical`, `Organized`, `Social`, `Creative`.

**Regeln für das Scoring:**
*   **Balance:** Eine Antwort sollte nicht mehr als 2-3 Traits gleichzeitig beeinflussen (Fokus behalten).
*   **Gewichtung:** Ein Wert von `+2` oder `+3` steht für eine sehr starke Ausprägung, `+1` für eine subtile Tendenz.
*   **Neutralität:** Es gibt keine "falschen" Antworten. Jede Antwort fügt nur Punkte hinzu (kein Abzug).

### Kategorisierung
Überlege dir, wo die Frage hingehört:
*   **Charakter-Fragen (Step 2):** "Wer bin ich?" (z. B. Alltagsgewohnheiten, Reaktionen auf Stress).
*   **Partner-Fragen (Step 3):** "Was will ich?" (z. B. Idealvorstellung eines Dates, Red Flags).

### Der Tonfall (Tone of Voice)
*   **Leichtigkeit:** Die Fragen sollten niemals zu ernst sein.
*   **Popkultur:** Bezüge zu aktuellen Trends, Filmen (wie High School Musical) oder Internet-Memes sind erwünscht.
*   **Inklusivität:** Formulierungen sollten so gewählt sein, dass sie unabhängig von Geschlecht oder Orientierung funktionieren.

---

## 7. Aktueller Fragenkatalog (Referenz)

### Teil 1: Charakter-Fragebogen (Selbstbild)
1.  **Friday night vibe?** (Antworten: Going Out, Cozy Night In, Dinner, Road Trip) -> Fokus: *Social vs. Homebody*
2.  **Biggest flex?** (Antworten: IKEA Master, Karaoke Pro, Duolingo, Fridge Chef) -> Fokus: *Organized, Creative*
3.  **Potato-Metapher?** (Antworten: Fries, Mashed, Hash Browns, Vodka) -> Fokus: *Social, Spontaneous*
4.  **Excuses for being late?** (Antworten: Basketball, Singing, Baking, Friends) -> Fokus: *Logical, Creative, Social*
5.  **Senior year priority?** (Antworten: Juilliard, Prom, Musical, Wildcats) -> Fokus: *Logical, Romantic, Creative, Social*
6.  **Handling stress?** (Antworten: Stare & Mist, Panic solve, Avoidance, Delegate) -> Fokus: *Homebody, Organized*

### Teil 2: Partner-Präferenzen (Wünsche)
1.  **Ideal first date?** (Antworten: Coffee Shop, Amusement Park, Fancy Dinner, Arcade)
2.  **Absolute red flag?** (Antworten: Doesn't like animals, Never texts back, No hobbies, Rude to waiters)
3.  **Attractive trait?** (Antworten: Humor, Intelligence, Practicality, Ambition)
4.  **High School Musical Crush?** (Antworten: Troy, Gabriella, Sharpay, Ryan)
5.  **Song to win your heart?** (Antworten: 2000s Pop, Ballad, Disney, Indie Rock)
6.  **Attractive Look?** (Wahl zwischen: Zac Efron, Vanessa Hudgens, Ashley Tisdale, Corbin Bleu)

---

# 🎭 Das Character Mapping System

Dieses System übersetzt die 8 Trait-Dimensionen in spezifische Archetypen der fünf Franchises. Das Ergebnis wird basierend auf dem **höchsten Score** (Primär-Trait) berechnet.

## 1. High School Musical (Die "Wildcats" Matrix)
| Primär-Trait | Charakter | Analyse-Text |
| :--- | :--- | :--- |
| **Social** | **Troy Bolton** | Du bist das Herz des Teams. Du jonglierst ständig zwischen Erwartungen und deinem Herzen. |
| **Romantic** | **Gabriella Montez** | Du bist tiefgründig und suchst nach echter Verbindung. "Breaking Free" ist dein Lebensmotto. |
| **Organized** | **Sharpay Evans** | Du weißt, was du willst, und hast den 5-Jahres-Plan schon fertig. |
| **Creative** | **Ryan Evans** | Du bist das unterschätzte Genie hinter den Kulissen. Ohne deine Vision gäbe es keine Show. |
| **Logical** | **Taylor McKessie** | Du bist der Kopf der Gruppe. Während andere tanzen, hast du schon die Weltrettung geplant. |

## 2. Harry Potter (Die "Wizarding" Matrix)
| Primär-Trait | Charakter | Analyse-Text |
| :--- | :--- | :--- |
| **Adventurous** | **Harry Potter** | Du handelst nach Instinkt und stürzt dich in jedes Abenteuer – oft ohne Plan, aber mit viel Herz. |
| **Logical** | **Hermine Granger** | Du bist brillant, strukturiert und die Rettung in jeder Notlage. |
| **Homebody** | **Ron Weasley** | Loyalität, ein gemütlicher Pulli und ein gutes Essen sind dir wichtiger als jeder Ruhm. |
| **Creative** | **Luna Lovegood** | Du siehst Nargel, wo andere nur Luft sehen. Deine Einzigartigkeit ist deine Stärke. |
| **Organized** | **Draco Malfoy** | Du achtest auf deinen Ruf und spielst das Spiel des Lebens nach deinen eigenen Regeln. |

## 3. Phineas & Ferb (Die "Inator" Matrix)
| Primär-Trait | Charakter | Analyse-Text |
| :--- | :--- | :--- |
| **Creative** | **Phineas Flynn** | "Ferb, ich weiß, was wir heute tun!" Dein Optimismus kennt keine Grenzen. |
| **Logical** | **Ferb Fletcher** | Du sagst nicht viel, aber wenn du etwas sagst, hat es Gewicht. Ein Macher der leisen Töne. |
| **Spontaneous** | **Perry (Agent P)** | Du führst ein Doppelleben zwischen totaler Entspannung und hochriskanten Geheimmissionen. |
| **Organized** | **Candace Flynn** | Du liebst Ordnung und willst, dass alle anderen sich an die Regeln halten. |
| **Creative (Dark)**| **Dr. Doofenshmirtz**| Du hast brillante Ideen, aber manchmal steht dir deine eigene dramatische Backstory im Weg. |

## 4. Herr der Ringe (Die "Gefährten" Matrix)
| Primär-Trait | Charakter | Analyse-Text |
| :--- | :--- | :--- |
| **Adventurous** | **Aragorn** | Ein Wanderer, der keine Angst vor der Dunkelheit hat. Eine geborene Führungspersönlichkeit. |
| **Homebody** | **Samweis Gamdschie** | Du bist der Fels in der Brandung. Ohne dich würde niemand das Ziel erreichen. |
| **Social** | **Pippin & Merry** | Du bringst Licht in dunkle Zeiten und weißt, dass ein zweites Frühstück lebensnotwendig ist. |
| **Logical** | **Elrond / Galadriel** | Du blickst über den Tellerrand hinaus und planst in Jahrhunderten, nicht in Tagen. |
| **Romantic** | **Arwen** | Für die wahre Liebe würdest du alles aufgeben – sogar deine Unsterblichkeit. |

## 5. Bernd das Brot (Die "MIST!" Matrix)
| Primär-Trait | Charakter | Analyse-Text |
| :--- | :--- | :--- |
| **Homebody** | **Bernd das Brot** | Du liebst deine Raufasertapete und hast eine Abneigung gegen unnötigen Enthusiasmus. |
| **Social** | **Chili das Schaf** | Du bist das totale Gegenteil von Bernd: Hyperaktiv, laut und immer auf der Suche nach dem nächsten Stunt. |
| **Creative** | **Briegel der Busch** | Du bist ein genialer Erfinder, aber deine Experimente enden meistens in einer Explosion. |

---

## 🛠️ Technische Umsetzung (Vorschlag)
1.  **Start:** User wählt Lieblings-Franchise (z. B. „Harry Potter“).
2.  **Quiz:** Fragenpool durchlaufen (z. B. 10-15 zufällige Fragen).
3.  **Calculation:** Traits werden summiert.
4.  **Result:** 
    *   *Choice == 'Harry Potter' && MaxTrait == 'Logical'* -> **Hermine.**
    *   *Wildcard-Modus:* Mix aus verschiedenen Franchises (z. B. "Mut von Harry, Planung von Sharpay").
