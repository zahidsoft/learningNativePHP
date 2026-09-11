import { Head } from '@inertiajs/react';
import type { CSSProperties } from 'react';
import { useEffect, useMemo, useState } from 'react';

type Letter = { ch: string; name: string; word?: string };
type CatId = 'english' | 'arabic' | 'numbers' | 'consonants' | 'vowels';
type Cat = {
    id: CatId;
    title: string;
    sub: string;
    bg: string;
    soft: string;
    lang: string;
    letters: Letter[];
};

const PAL = ['#ee2f5a', '#1f7ae0', '#f9a11b', '#3aa93a', '#8b3ff0', '#d62b74'];

const p = (s: string, fallback = ''): Letter[] =>
    s.split('|').map((row) => {
        const b = row.split(':');
        return { ch: b[0], name: b[1], word: b[2] || fallback };
    });

const CATS: Record<CatId, Cat> = {
    english: {
        id: 'english',
        title: 'English Letters',
        sub: 'abc · A to Z',
        bg: '#1f7ae0',
        soft: '#e8f3ff',
        lang: 'en-US',
        letters: p(
            'A:Ay:Apple|B:Bee:Ball|C:See:Cat|D:Dee:Duck|E:Ee:Egg|F:Ef:Fish|G:Jee:Goat|H:Aitch:Hat|I:Eye:Ice cream|J:Jay:Jug|K:Kay:Kite|L:El:Lion|M:Em:Moon|N:En:Nest|O:Oh:Owl|P:Pee:Pencil|Q:Queue:Queen|R:Ar:Rain|S:Es:Sun|T:Tee:Tree|U:You:Umbrella|V:Vee:Van|W:Double-u:Water|X:Ex:Box|Y:Wy:Yarn|Z:Zed:Zebra',
        ),
    },
    arabic: {
        id: 'arabic',
        title: 'Arabic Letters',
        sub: 'Alif Ba · 28 letters',
        bg: '#1f8b3a',
        soft: '#eafaea',
        lang: 'ar-SA',
        letters: p(
            'ا:Alif|ب:Ba|ت:Ta|ث:Tha|ج:Jim|ح:Ha|خ:Kha|د:Dal|ذ:Dhal|ر:Ra|ز:Zay|س:Sin|ش:Shin|ص:Sad|ض:Dad|ط:Taa|ظ:Zaa|ع:Ain|غ:Ghain|ف:Fa|ق:Qaf|ك:Kaf|ل:Lam|م:Mim|ن:Nun|ه:Haa|و:Waw|ي:Ya',
            'Arabic alphabet',
        ),
    },
    numbers: {
        id: 'numbers',
        title: 'Numbers',
        sub: '1, 2, 3, 4 · 1 to 20',
        bg: '#d69200',
        soft: '#fff6e0',
        lang: 'en-US',
        letters: p(
            '1:One:১|2:Two:২|3:Three:৩|4:Four:৪|5:Five:৫|6:Six:৬|7:Seven:৭|8:Eight:৮|9:Nine:৯|10:Ten:১০|11:Eleven:১১|12:Twelve:১২|13:Thirteen:১৩|14:Fourteen:১৪|15:Fifteen:১৫|16:Sixteen:১৬|17:Seventeen:১৭|18:Eighteen:১৮|19:Nineteen:১৯|20:Twenty:২০',
        ),
    },
    consonants: {
        id: 'consonants',
        title: 'Bangla Consonants',
        sub: 'ক, খ, গ · ব্যঞ্জনবর্ণ',
        bg: '#6b2fd6',
        soft: '#f3edff',
        lang: 'bn-BD',
        letters: p(
            'ক:ko|খ:kho|গ:go|ঘ:gho|ঙ:ungo|চ:cho|ছ:chho|জ:borgio jo|ঝ:jho|ঞ:ingo|ট:to|ঠ:tho|ড:do|ঢ:dho|ণ:murdhonno no|ত:to|থ:tho|দ:do|ধ:dho|ন:donto no|প:po|ফ:pho|ব:bo|ভ:bho|ম:mo|য:ontosto jo|র:ro|ল:lo|শ:talobbo sho|ষ:murdhonno sho|স:donto so|হ:ho',
            'ব্যঞ্জনবর্ণ',
        ),
    },
    vowels: {
        id: 'vowels',
        title: 'Bangla Vowels',
        sub: 'অ, আ ই · স্বরবর্ণ',
        bg: '#d62b74',
        soft: '#fff0f6',
        lang: 'bn-BD',
        letters: p(
            'অ:o|আ:a|ই:i|ঈ:dirgho i|উ:u|ঊ:dirgho u|ঋ:ri|এ:e|ঐ:oi|ও:o|ঔ:ou',
            'স্বরবর্ণ',
        ),
    },
};

const ALL: (Letter & { lang: string })[] = (
    Object.keys(CATS) as CatId[]
).flatMap((k) => CATS[k].letters.map((l) => ({ ...l, lang: CATS[k].lang })));
const shuffle = <T,>(a: T[]): T[] =>
    a
        .map((v) => [Math.random(), v] as const)
        .sort((x, y) => x[0] - y[0])
        .map((v) => v[1]);
const makeQ = () => {
    const pool = shuffle(ALL);
    const answer = pool[0];
    return {
        answer,
        opts: shuffle([
            answer,
            ...pool.filter((l) => l.name !== answer.name).slice(0, 3),
        ]),
    };
};

// Calls into the native Speech.Speak bridge function (a small NativePHP
// plugin wrapping android.speech.tts.TextToSpeech / iOS's
// AVSpeechSynthesizer — see plugins/speech). Android's System WebView does
// not implement window.speechSynthesis, so "Tap to hear" needs this instead
// of the Web Speech API.
async function nativeBridgeCall(
    method: string,
    params: Record<string, unknown> = {},
): Promise<unknown> {
    const response = await fetch('/_native/api/call', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
        },
        body: JSON.stringify({ method, params }),
    });
    const result = await response.json();
    if (result.status === 'error') {
        throw new Error(result.message || 'Native call failed');
    }
    const data = result.data;
    return data && typeof data === 'object' && 'data' in data
        ? data.data
        : data;
}

// A screen transition pushed onto browser history, so the phone's hardware
// back button (which the native shell maps to WebView.goBack() when history
// exists, or exits the app otherwise) steps back through our screens the
// same way the on-screen back arrow does, instead of quitting the app from
// any depth.
type NavState = {
    screen: 'home' | 'cat' | 'letter' | 'quiz';
    catId: CatId | null;
    idx: number;
};

const STICKER =
    '3px 3px 0 #fff,-3px 3px 0 #fff,3px -3px 0 #fff,-3px -3px 0 #fff,0 7px 6px rgba(0,0,0,.16)';
const S: Record<string, CSSProperties> = {
    shell: {
        width: '100%',
        maxWidth: 430,
        margin: '0 auto',
        minHeight: '100dvh',
        position: 'relative',
        overflow: 'hidden',
        background: '#eaf6ff',
        fontFamily: "'Nunito',system-ui,sans-serif",
    },
    screen: {
        position: 'absolute',
        inset: 0,
        display: 'flex',
        flexDirection: 'column',
    },
    sky: {
        position: 'absolute',
        inset: 0,
        display: 'flex',
        flexDirection: 'column',
        background:
            'linear-gradient(168deg,#f6f9ff 0%,#eef4fd 46%,#f7f3ff 100%)',
    },
    blooms: {
        position: 'absolute',
        inset: 0,
        background:
            'radial-gradient(circle 220px at 108% 16%,rgba(94,162,255,.16),rgba(94,162,255,0) 70%),' +
            'radial-gradient(circle 200px at -12% 46%,rgba(255,168,86,.13),rgba(255,168,86,0) 70%),' +
            'radial-gradient(circle 240px at 90% 96%,rgba(138,96,240,.13),rgba(138,96,240,0) 70%)',
    },
    dots: {
        position: 'absolute',
        inset: 0,
        opacity: 0.5,
        background:
            'radial-gradient(circle 1.4px at 1.4px 1.4px,rgba(38,74,130,.18) 1.4px,rgba(0,0,0,0) 1.5px) 0 0/22px 22px',
    },
    fade: {
        position: 'absolute',
        left: 0,
        right: 0,
        bottom: 0,
        height: 150,
        background:
            'linear-gradient(180deg,rgba(247,249,255,0),rgba(255,255,255,.6))',
    },
    scroll: {
        flex: 1,
        minHeight: 0,
        overflowY: 'auto',
        padding: '4px 16px 24px',
    } as CSSProperties,
    grid: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 },
    glyphRow: {
        position: 'relative',
        height: 78,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 2,
    },
    pillIcon: {
        width: 28,
        height: 28,
        flex: 'none',
        borderRadius: '50%',
        background: 'rgba(255,255,255,.22)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 2,
    },
    back: {
        width: 40,
        height: 40,
        flex: 'none',
        borderRadius: '50%',
        background: '#fff',
        boxShadow: '0 3px 0 rgba(0,0,0,.14)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        cursor: 'pointer',
        border: 0,
    },
    chev: {
        display: 'block',
        width: 11,
        height: 11,
        borderLeft: '4px solid #2c3e58',
        borderBottom: '4px solid #2c3e58',
        transform: 'rotate(45deg)',
        marginLeft: -3,
    },
    ph: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        font: '700 7px ui-monospace,monospace',
        textAlign: 'center',
        lineHeight: 1.1,
    },
};

const card = (
    from: string,
    to: string,
    shadow: string,
    wide = false,
): CSSProperties => ({
    gridColumn: wide ? '1 / -1' : undefined,
    position: 'relative',
    overflow: 'hidden',
    borderRadius: 24,
    padding: '12px 10px 10px',
    background: `linear-gradient(180deg,${from},${to})`,
    boxShadow: `0 6px 0 ${shadow}`,
    cursor: 'pointer',
    border: 0,
    width: '100%',
    textAlign: 'left',
    font: 'inherit',
});
const pill = (bg: string, shadow: string): CSSProperties => ({
    position: 'relative',
    marginTop: 8,
    borderRadius: 999,
    background: bg,
    boxShadow: `0 3px 0 ${shadow}`,
    padding: '7px 10px',
    display: 'flex',
    alignItems: 'center',
    gap: 8,
});
const glyph = (color: string, size: number, family: string): CSSProperties => ({
    color,
    font: `${family === "'Baloo 2'" ? 800 : 700} ${size}px/1 ${family}`,
    textShadow: STICKER,
});

const WaveDefs = () => (
    <svg
        width={0}
        height={0}
        style={{ position: 'absolute' }}
        aria-hidden="true"
    >
        <defs>
            <clipPath id="hdrWave" clipPathUnits="objectBoundingBox">
                <path d="M0,0 L1,0 L1,0.82 C0.84,1 0.66,0.76 0.5,0.85 C0.33,0.94 0.16,1.02 0,0.86 Z" />
            </clipPath>
        </defs>
    </svg>
);

const HeaderCrest = () => (
    <svg
        viewBox="0 0 390 48"
        preserveAspectRatio="none"
        style={{
            position: 'absolute',
            left: 0,
            right: 0,
            bottom: 14,
            width: '100%',
            height: 48,
            display: 'block',
        }}
    >
        <path
            d="M0,30 C62,52 124,8 198,22 C266,36 332,50 390,30 L390,48 L0,48 Z"
            fill="rgba(255,255,255,0.17)"
        />
    </svg>
);

const BookIcon = () => (
    <span style={S.pillIcon}>
        <span
            style={{
                display: 'block',
                width: 6,
                height: 13,
                borderRadius: '2px 0 0 2px',
                background: '#fff',
            }}
        />
        <span
            style={{
                display: 'block',
                width: 6,
                height: 13,
                borderRadius: '0 2px 2px 0',
                background: '#fff',
            }}
        />
    </span>
);

export default function Welcome() {
    const [screen, setScreen] = useState<'home' | 'cat' | 'letter' | 'quiz'>(
        'home',
    );
    const [q, setQ] = useState(makeQ);
    const [qNum, setQNum] = useState(1);
    const [score, setScore] = useState(0);
    const [picked, setPicked] = useState<string | null>(null);
    const [catId, setCatId] = useState<CatId | null>(null);
    const [idx, setIdx] = useState(0);

    const cat = catId ? CATS[catId] : null;
    const list = cat ? cat.letters : [];
    const cur = list[idx] ?? { ch: '', name: '', word: '' };

    // Restore the previous screen whenever browser history is popped —
    // by the on-screen back arrows (via goBack, below) or by the phone's
    // hardware back button walking WebView history.
    useEffect(() => {
        if (typeof window === 'undefined') return;

        if (!window.history.state || window.history.state.screen === undefined) {
            const initial: NavState = { screen: 'home', catId: null, idx: 0 };
            window.history.replaceState(initial, '');
        }

        const onPopState = (e: PopStateEvent) => {
            const s = e.state as NavState | null;
            if (!s) return;
            setScreen(s.screen);
            setCatId(s.catId);
            setIdx(s.idx);
        };

        window.addEventListener('popstate', onPopState);
        return () => window.removeEventListener('popstate', onPopState);
    }, []);

    const pushScreen = (next: NavState) => {
        setScreen(next.screen);
        setCatId(next.catId);
        setIdx(next.idx);
        window.history?.pushState(next, '');
        buzz();
    };
    const goBack = () => {
        window.history.back();
    };

    // A short native tap buzz (Device.Vibrate is core to nativephp/mobile,
    // no plugin needed) on meaningful selections — silently does nothing
    // outside the native shell (desktop browser dev preview).
    const buzz = () => {
        nativeBridgeCall('Device.Vibrate').catch(() => {});
    };

    const say = (text: string, lang?: string) => {
        const speakLang = lang || 'en-US';
        nativeBridgeCall('Speech.Speak', { text, lang: speakLang, rate: 0.85 }).catch(() => {
            // Not running inside the native shell (e.g. `npm run dev` in a
            // desktop browser) — fall back to the Web Speech API.
            if (typeof window === 'undefined' || !window.speechSynthesis) return;
            try {
                window.speechSynthesis.cancel();
                const u = new SpeechSynthesisUtterance(text);
                u.lang = speakLang;
                u.rate = 0.8;
                window.speechSynthesis.speak(u);
            } catch {
                /* speech not available in this webview */
            }
        });
    };

    const open = (id: CatId) => {
        pushScreen({ screen: 'cat', catId: id, idx: 0 });
    };
    const step = (d: number) => {
        const n = list.length;
        if (!n) return;
        const i = (idx + d + n) % n;
        setIdx(i);
        say(list[i].name, cat?.lang);
    };
    const tiles = useMemo(
        () => list.map((l, i) => ({ ...l, color: PAL[i % PAL.length], i })),
        [list],
    );

    return (
        <>
            <Head title="Learn & Grow">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link
                    rel="preconnect"
                    href="https://fonts.gstatic.com"
                    crossOrigin=""
                />
                <link
                    href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Baloo+Da+2:wght@600;700;800&family=Noto+Naskh+Arabic:wght@700&family=Nunito:wght@600;700;800&display=swap"
                    rel="stylesheet"
                />
                <style>{`
                    #app { min-height: 100%; }
                    *::-webkit-scrollbar { width: 0; height: 0; display: none; }
                    * { scrollbar-width: none; -ms-overflow-style: none; }
                    html, body { overscroll-behavior: none; touch-action: manipulation; }
                    * { -webkit-tap-highlight-color: transparent; -webkit-touch-callout: none; }
                    button { -webkit-user-select: none; user-select: none; }
                `}</style>
            </Head>

            {screen === 'home' && (
                <div style={S.shell}>
                    <div style={S.sky}>
                        <div style={S.blooms} />
                        <div style={S.dots} />
                        <div style={S.fade} />
                        <WaveDefs />
                        <div
                            style={
                                {
                                    position: 'relative',
                                    padding: '34px 18px 74px',
                                    background:
                                        'linear-gradient(180deg,#1478e6 0%,#2b9bf4 55%,#57b6fa 100%)',
                                    flex: 'none',
                                    clipPath: 'url(#hdrWave)',
                                    WebkitClipPath: 'url(#hdrWave)',
                                } as CSSProperties
                            }
                        >
                            <div
                                style={{
                                    position: 'absolute',
                                    right: -30,
                                    top: -40,
                                    width: 160,
                                    height: 160,
                                    borderRadius: '50%',
                                    background: 'rgba(255,255,255,.08)',
                                }}
                            />
                            <div
                                style={{
                                    position: 'absolute',
                                    left: -40,
                                    top: 30,
                                    width: 120,
                                    height: 120,
                                    borderRadius: '50%',
                                    background: 'rgba(255,255,255,.07)',
                                }}
                            />
                            <div
                                style={{
                                    position: 'relative',
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 12,
                                }}
                            >
                                <div
                                    style={{
                                        ...S.ph,
                                        width: 52,
                                        height: 52,
                                        flex: 'none',
                                        borderRadius: 14,
                                        border: '3px solid #2f7d32',
                                        color: '#2f7d32',
                                        background:
                                            'repeating-linear-gradient(135deg,#fff,#fff 5px,#e6f2ff 5px,#e6f2ff 10px)',
                                    }}
                                >
                                    logo
                                    <br />
                                    art
                                </div>
                                <div style={{ flex: 1, minWidth: 0 }}>
                                    <div
                                        style={{
                                            font: "800 31px/1 'Baloo 2'",
                                            color: '#fff',
                                            letterSpacing: '-.5px',
                                            textShadow:
                                                '0 3px 0 rgba(12,74,130,.35)',
                                        }}
                                    >
                                        Learn{' '}
                                        <span style={{ color: '#ffd23f' }}>
                                            &amp;
                                        </span>{' '}
                                        Grow
                                    </div>
                                    <div
                                        style={{
                                            font: "700 13px 'Nunito'",
                                            color: '#eaf6ff',
                                            marginTop: 3,
                                        }}
                                    >
                                        Small Steps &nbsp;Big Future
                                    </div>
                                </div>
                                <button
                                    aria-label="Settings"
                                    style={{
                                        width: 44,
                                        height: 44,
                                        flex: 'none',
                                        borderRadius: '50%',
                                        background: '#fff',
                                        boxShadow:
                                            '0 4px 0 rgba(12,74,130,.25)',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        border: 0,
                                        cursor: 'pointer',
                                    }}
                                >
                                    <span
                                        style={{
                                            position: 'relative',
                                            width: 26,
                                            height: 26,
                                            borderRadius: '50%',
                                            background:
                                                'repeating-conic-gradient(from 11.25deg,#2c3e58 0 22.5deg,rgba(0,0,0,0) 22.5deg 45deg)',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                        }}
                                    >
                                        <span
                                            style={{
                                                width: 20,
                                                height: 20,
                                                borderRadius: '50%',
                                                background: '#2c3e58',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                            }}
                                        >
                                            <span
                                                style={{
                                                    width: 7,
                                                    height: 7,
                                                    borderRadius: '50%',
                                                    background: '#fff',
                                                }}
                                            />
                                        </span>
                                    </span>
                                </button>
                            </div>
                            <HeaderCrest />
                        </div>

                        <div
                            style={{
                                ...S.scroll,
                                position: 'relative',
                                padding: '8px 16px 18px',
                            }}
                        >
                            <div
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 10,
                                    padding: '2px 0 14px',
                                }}
                            >
                                <div
                                    style={{
                                        ...S.ph,
                                        width: 66,
                                        height: 66,
                                        flex: 'none',
                                        borderRadius: 18,
                                        color: '#4a7ba8',
                                        background:
                                            'repeating-linear-gradient(45deg,#fff,#fff 5px,#dceeff 5px,#dceeff 10px)',
                                    }}
                                >
                                    kid
                                    <br />
                                    mascot
                                </div>
                                <div>
                                    <div
                                        style={{
                                            font: "800 27px/1 'Baloo 2'",
                                            color: '#173d6b',
                                        }}
                                    >
                                        Welcome!
                                    </div>
                                    <div
                                        style={{
                                            font: "700 13.5px 'Nunito'",
                                            color: '#3a6390',
                                            marginTop: 4,
                                        }}
                                    >
                                        Choose a category to start learning
                                    </div>
                                </div>
                            </div>

                            <div style={S.grid}>
                                <button
                                    onClick={() => open('english')}
                                    style={card(
                                        '#d6ecff',
                                        '#a9d8fb',
                                        'rgba(31,122,224,.18)',
                                    )}
                                >
                                    <div
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            font: "800 34px 'Baloo 2'",
                                            color: 'rgba(31,122,224,.13)',
                                            padding: '6px 10px',
                                            display: 'flex',
                                            justifyContent: 'space-between',
                                        }}
                                    >
                                        A<span>B</span>
                                    </div>
                                    <div style={S.glyphRow}>
                                        <span
                                            style={glyph(
                                                '#ee2f5a',
                                                50,
                                                "'Baloo 2'",
                                            )}
                                        >
                                            A
                                        </span>
                                        <span
                                            style={glyph(
                                                '#1f7ae0',
                                                50,
                                                "'Baloo 2'",
                                            )}
                                        >
                                            B
                                        </span>
                                        <span
                                            style={glyph(
                                                '#f9b21b',
                                                50,
                                                "'Baloo 2'",
                                            )}
                                        >
                                            C
                                        </span>
                                    </div>
                                    <div
                                        style={pill(
                                            '#1f7ae0',
                                            'rgba(12,60,120,.35)',
                                        )}
                                    >
                                        <BookIcon />
                                        <div>
                                            <div
                                                style={{
                                                    font: "800 17px/1 'Baloo 2'",
                                                    color: '#fff',
                                                }}
                                            >
                                                abc
                                            </div>
                                            <div
                                                style={{
                                                    font: "800 10.5px 'Nunito'",
                                                    color: '#e6f2ff',
                                                    marginTop: 2,
                                                }}
                                            >
                                                English Letters
                                            </div>
                                        </div>
                                    </div>
                                </button>

                                <button
                                    onClick={() => open('arabic')}
                                    style={card(
                                        '#c9f0c2',
                                        '#8ddc86',
                                        'rgba(45,125,45,.2)',
                                    )}
                                >
                                    <div
                                        style={{
                                            ...S.glyphRow,
                                            direction: 'rtl',
                                            font: "700 48px/1 'Noto Naskh Arabic'",
                                            color: '#14682a',
                                            textShadow: STICKER,
                                        }}
                                    >
                                        ا ب ت
                                    </div>
                                    <div
                                        style={pill(
                                            '#1f8b3a',
                                            'rgba(15,70,30,.4)',
                                        )}
                                    >
                                        <span style={S.pillIcon}>
                                            <span
                                                style={{
                                                    display: 'block',
                                                    width: 13,
                                                    height: 13,
                                                    borderRadius:
                                                        '50% 50% 3px 3px',
                                                    background: '#fff',
                                                }}
                                            />
                                        </span>
                                        <div>
                                            <div
                                                style={{
                                                    font: "800 14.5px/1 'Baloo 2'",
                                                    color: '#fff',
                                                }}
                                            >
                                                Arabic Letters
                                            </div>
                                            <div
                                                style={{
                                                    font: "800 10.5px 'Nunito'",
                                                    color: '#e4ffe8',
                                                    marginTop: 2,
                                                }}
                                            >
                                                Alif Ba
                                            </div>
                                        </div>
                                    </div>
                                </button>

                                <button
                                    onClick={() => open('numbers')}
                                    style={card(
                                        '#ffe08a',
                                        '#f9c23c',
                                        'rgba(190,130,10,.2)',
                                    )}
                                >
                                    <div
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            padding: '6px 10px',
                                            display: 'flex',
                                            justifyContent: 'space-between',
                                            font: "800 30px 'Baloo 2'",
                                            color: 'rgba(160,110,10,.16)',
                                        }}
                                    >
                                        5<span>8</span>
                                    </div>
                                    <div style={S.glyphRow}>
                                        <span
                                            style={glyph(
                                                '#ee2f30',
                                                48,
                                                "'Baloo 2'",
                                            )}
                                        >
                                            1
                                        </span>
                                        <span
                                            style={glyph(
                                                '#1f7ae0',
                                                48,
                                                "'Baloo 2'",
                                            )}
                                        >
                                            2
                                        </span>
                                        <span
                                            style={glyph(
                                                '#5fb316',
                                                48,
                                                "'Baloo 2'",
                                            )}
                                        >
                                            3
                                        </span>
                                        <span
                                            style={glyph(
                                                '#8b3ff0',
                                                48,
                                                "'Baloo 2'",
                                            )}
                                        >
                                            4
                                        </span>
                                    </div>
                                    <div
                                        style={pill(
                                            '#d69200',
                                            'rgba(130,88,0,.4)',
                                        )}
                                    >
                                        <span style={S.pillIcon}>
                                            <span
                                                style={{
                                                    display: 'block',
                                                    width: 13,
                                                    height: 13,
                                                    borderRadius: 3,
                                                    background: '#fff',
                                                }}
                                            />
                                        </span>
                                        <div>
                                            <div
                                                style={{
                                                    font: "800 15px/1 'Baloo 2'",
                                                    color: '#fff',
                                                }}
                                            >
                                                1, 2, 3, 4
                                            </div>
                                            <div
                                                style={{
                                                    font: "800 10.5px 'Nunito'",
                                                    color: '#fff5df',
                                                    marginTop: 2,
                                                }}
                                            >
                                                Numbers
                                            </div>
                                        </div>
                                    </div>
                                </button>

                                <button
                                    onClick={() => open('consonants')}
                                    style={card(
                                        '#e2d6ff',
                                        '#c3a9fb',
                                        'rgba(100,60,190,.2)',
                                    )}
                                >
                                    <div
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            padding: '6px 10px',
                                            display: 'flex',
                                            justifyContent: 'space-between',
                                            font: "700 28px 'Baloo Da 2'",
                                            color: 'rgba(90,50,180,.16)',
                                        }}
                                    >
                                        ব<span>স</span>
                                    </div>
                                    <div style={{ ...S.glyphRow, gap: 6 }}>
                                        <span
                                            style={glyph(
                                                '#ee2f6f',
                                                44,
                                                "'Baloo Da 2'",
                                            )}
                                        >
                                            ক
                                        </span>
                                        <span
                                            style={glyph(
                                                '#1f7ae0',
                                                44,
                                                "'Baloo Da 2'",
                                            )}
                                        >
                                            খ
                                        </span>
                                        <span
                                            style={glyph(
                                                '#3aa93a',
                                                44,
                                                "'Baloo Da 2'",
                                            )}
                                        >
                                            গ
                                        </span>
                                    </div>
                                    <div
                                        style={pill(
                                            '#6b2fd6',
                                            'rgba(55,20,120,.4)',
                                        )}
                                    >
                                        <BookIcon />
                                        <div>
                                            <div
                                                style={{
                                                    font: "700 15px/1 'Baloo Da 2'",
                                                    color: '#fff',
                                                }}
                                            >
                                                ক, খ, গ
                                            </div>
                                            <div
                                                style={{
                                                    font: "800 10px 'Nunito'",
                                                    color: '#f0e7ff',
                                                    marginTop: 2,
                                                }}
                                            >
                                                বাংলা ব্যঞ্জনবর্ণ
                                            </div>
                                        </div>
                                    </div>
                                </button>

                                <button
                                    onClick={() => open('vowels')}
                                    style={card(
                                        '#ffdcec',
                                        '#ffb9d6',
                                        'rgba(200,60,120,.18)',
                                        true,
                                    )}
                                >
                                    <div
                                        style={{
                                            position: 'absolute',
                                            inset: 0,
                                            padding: '8px 14px',
                                            display: 'flex',
                                            justifyContent: 'space-between',
                                            font: "700 30px 'Baloo Da 2'",
                                            color: 'rgba(200,50,110,.16)',
                                        }}
                                    >
                                        অ<span>আ</span>
                                    </div>
                                    <div style={{ ...S.glyphRow, gap: 16 }}>
                                        <span
                                            style={glyph(
                                                '#ee2f5a',
                                                46,
                                                "'Baloo Da 2'",
                                            )}
                                        >
                                            অ
                                        </span>
                                        <span
                                            style={glyph(
                                                '#1f7ae0',
                                                46,
                                                "'Baloo Da 2'",
                                            )}
                                        >
                                            আ
                                        </span>
                                        <span
                                            style={glyph(
                                                '#f9a11b',
                                                46,
                                                "'Baloo Da 2'",
                                            )}
                                        >
                                            ই
                                        </span>
                                    </div>
                                    <div
                                        style={{
                                            position: 'relative',
                                            marginTop: 6,
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: 10,
                                        }}
                                    >
                                        <div
                                            style={{
                                                ...pill(
                                                    '#d62b74',
                                                    'rgba(140,20,70,.4)',
                                                ),
                                                flex: 1,
                                                marginTop: 0,
                                            }}
                                        >
                                            <BookIcon />
                                            <div>
                                                <div
                                                    style={{
                                                        font: "700 15px/1 'Baloo Da 2'",
                                                        color: '#fff',
                                                    }}
                                                >
                                                    অ, আ ই
                                                </div>
                                                <div
                                                    style={{
                                                        font: "800 10.5px 'Nunito'",
                                                        color: '#ffe6f1',
                                                        marginTop: 2,
                                                    }}
                                                >
                                                    বাংলা স্বরবর্ণ
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            style={{
                                                width: 52,
                                                height: 52,
                                                flex: 'none',
                                                borderRadius: 14,
                                                background: '#fff',
                                                boxShadow:
                                                    '0 3px 0 rgba(180,50,110,.25)',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                            }}
                                        >
                                            <svg
                                                viewBox="0 0 24 24"
                                                width={30}
                                                height={30}
                                                fill="none"
                                                stroke="#d62b74"
                                                strokeWidth={2}
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                            >
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7.5 18.5 3 20l1.5-4.5Z" />
                                                <path d="M14.5 5.5l3 3" />
                                            </svg>
                                        </div>
                                    </div>
                                </button>

                                <button
                                    onClick={() => {
                                        setQ(makeQ());
                                        setQNum(1);
                                        setScore(0);
                                        setPicked(null);
                                        pushScreen({ screen: 'quiz', catId, idx });
                                    }}
                                    style={{
                                        gridColumn: '1 / -1',
                                        position: 'relative',
                                        overflow: 'hidden',
                                        borderRadius: 24,
                                        padding: '16px 18px',
                                        background:
                                            'linear-gradient(120deg,#2a2f6b 0%,#4b3ea8 55%,#6d4bd6 100%)',
                                        boxShadow: '0 6px 0 rgba(35,25,90,.3)',
                                        cursor: 'pointer',
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: 14,
                                        border: 0,
                                        width: '100%',
                                        textAlign: 'left',
                                    }}
                                >
                                    <span
                                        style={{
                                            position: 'absolute',
                                            right: -24,
                                            top: -30,
                                            width: 120,
                                            height: 120,
                                            borderRadius: '50%',
                                            background: 'rgba(255,255,255,.08)',
                                        }}
                                    />
                                    <span
                                        style={{
                                            position: 'relative',
                                            width: 52,
                                            height: 52,
                                            flex: 'none',
                                            borderRadius: 16,
                                            background: '#ffd23f',
                                            boxShadow:
                                                '0 4px 0 rgba(0,0,0,.22)',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            font: "800 30px/1 'Baloo 2'",
                                            color: '#2a2f6b',
                                        }}
                                    >
                                        ?
                                    </span>
                                    <span
                                        style={{
                                            position: 'relative',
                                            flex: 1,
                                            minWidth: 0,
                                        }}
                                    >
                                        <span
                                            style={{
                                                display: 'block',
                                                font: "800 21px/1 'Baloo 2'",
                                                color: '#fff',
                                            }}
                                        >
                                            Quiz Time
                                        </span>
                                        <span
                                            style={{
                                                display: 'block',
                                                font: "800 11.5px 'Nunito'",
                                                color: '#d6d2ff',
                                                marginTop: 4,
                                            }}
                                        >
                                            Test what you learned · all
                                            categories
                                        </span>
                                    </span>
                                    <span
                                        style={{
                                            position: 'relative',
                                            borderRadius: 999,
                                            background: '#ffd23f',
                                            boxShadow:
                                                '0 3px 0 rgba(0,0,0,.22)',
                                            padding: '9px 16px',
                                            font: "800 13px 'Baloo 2'",
                                            color: '#2a2f6b',
                                        }}
                                    >
                                        Play
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {screen === 'quiz' &&
                (() => {
                    const correct = !!picked && picked === q.answer.name;
                    const pick = (name: string) => {
                        if (picked) return;
                        const ok = name === q.answer.name;
                        buzz();
                        say(ok ? 'Correct' : 'Try again', 'en-US');
                        setPicked(name);
                        if (ok) setScore((v) => v + 1);
                    };
                    const nextQ = () => {
                        if (qNum >= 10) {
                            goBack();
                            setQNum(1);
                            setScore(0);
                        } else {
                            setQNum((v) => v + 1);
                        }
                        setQ(makeQ());
                        setPicked(null);
                    };
                    const optStyle = (name: string): CSSProperties => {
                        let bg = 'rgba(255,255,255,.94)';
                        let fg = '#2a2f6b';
                        let shadow = 'rgba(0,0,0,.22)';
                        if (picked) {
                            if (name === q.answer.name) {
                                bg = '#3ec46a';
                                fg = '#fff';
                                shadow = '#23874a';
                            } else if (name === picked) {
                                bg = '#ef476f';
                                fg = '#fff';
                                shadow = '#a52948';
                            } else {
                                bg = 'rgba(255,255,255,.5)';
                                fg = '#4a4a7a';
                            }
                        }
                        return {
                            cursor: 'pointer',
                            textAlign: 'center',
                            borderRadius: 20,
                            padding: '15px 8px',
                            background: bg,
                            boxShadow: `0 4px 0 ${shadow}`,
                            font: "800 15px 'Baloo 2'",
                            color: fg,
                            border: 0,
                        };
                    };
                    return (
                        <div style={S.shell}>
                            <div
                                style={{
                                    ...S.screen,
                                    background:
                                        'linear-gradient(170deg,#232a63 0%,#3b3591 55%,#5a3fbe 100%)',
                                }}
                            >
                                <div
                                    style={{
                                        flex: 'none',
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: 12,
                                        padding: '22px 18px 8px',
                                    }}
                                >
                                    <button
                                        aria-label="Back"
                                        onClick={goBack}
                                        style={{
                                            ...S.back,
                                            background: 'rgba(255,255,255,.16)',
                                            boxShadow: 'none',
                                        }}
                                    >
                                        <span
                                            style={{
                                                ...S.chev,
                                                borderLeftColor: '#fff',
                                                borderBottomColor: '#fff',
                                            }}
                                        />
                                    </button>
                                    <div
                                        style={{
                                            flex: 1,
                                            font: "800 18px 'Baloo 2'",
                                            color: '#fff',
                                        }}
                                    >
                                        Quiz Time
                                    </div>
                                    <div
                                        style={{
                                            font: "800 12px 'Nunito'",
                                            color: '#ffd23f',
                                            background: 'rgba(0,0,0,.22)',
                                            padding: '7px 12px',
                                            borderRadius: 999,
                                        }}
                                    >
                                        Score {score}
                                    </div>
                                </div>

                                <div
                                    style={{
                                        flex: 'none',
                                        padding: '10px 20px 0',
                                    }}
                                >
                                    <div
                                        style={{
                                            height: 8,
                                            borderRadius: 999,
                                            background: 'rgba(255,255,255,.16)',
                                            overflow: 'hidden',
                                        }}
                                    >
                                        <div
                                            style={{
                                                height: '100%',
                                                borderRadius: 999,
                                                background: '#ffd23f',
                                                width: `${Math.round((qNum / 10) * 100)}%`,
                                            }}
                                        />
                                    </div>
                                    <div
                                        style={{
                                            font: "800 11px 'Nunito'",
                                            color: '#c7c2ff',
                                            marginTop: 8,
                                        }}
                                    >
                                        Question {qNum} of 10
                                    </div>
                                </div>

                                <div
                                    style={{
                                        flex: 1,
                                        minHeight: 0,
                                        display: 'flex',
                                        flexDirection: 'column',
                                        justifyContent: 'center',
                                        gap: 22,
                                        padding: '0 20px',
                                    }}
                                >
                                    <div
                                        style={{
                                            textAlign: 'center',
                                            font: "800 15px 'Nunito'",
                                            color: '#cfc9ff',
                                        }}
                                    >
                                        Which one is this letter?
                                    </div>
                                    <div
                                        key={qNum}
                                        style={{
                                            alignSelf: 'center',
                                            width: 190,
                                            height: 190,
                                            borderRadius: 48,
                                            background: 'rgba(255,255,255,.97)',
                                            boxShadow:
                                                '0 12px 0 rgba(0,0,0,.22)',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                        }}
                                    >
                                        <span
                                            style={{
                                                font: "700 110px/1 'Baloo Da 2','Noto Naskh Arabic','Baloo 2'",
                                                color: '#3b3591',
                                            }}
                                        >
                                            {q.answer.ch}
                                        </span>
                                    </div>
                                    <div
                                        style={{
                                            display: 'grid',
                                            gridTemplateColumns: '1fr 1fr',
                                            gap: 12,
                                        }}
                                    >
                                        {q.opts.map((o) => (
                                            <button
                                                key={o.name}
                                                onClick={() => pick(o.name)}
                                                style={optStyle(o.name)}
                                            >
                                                {o.name}
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                <div
                                    style={{
                                        flex: 'none',
                                        padding: '0 20px 30px',
                                        minHeight: 78,
                                        display: 'flex',
                                        alignItems: 'center',
                                    }}
                                >
                                    {picked && (
                                        <div
                                            style={{
                                                flex: 1,
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: 12,
                                            }}
                                        >
                                            <div
                                                style={{
                                                    flex: 1,
                                                    font: "800 15px 'Baloo 2'",
                                                    color: correct
                                                        ? '#8df0ae'
                                                        : '#ffb3c6',
                                                }}
                                            >
                                                {correct
                                                    ? 'Correct! Well done'
                                                    : `Oops — it was ${q.answer.name}`}
                                            </div>
                                            <button
                                                onClick={nextQ}
                                                style={{
                                                    cursor: 'pointer',
                                                    borderRadius: 18,
                                                    background: '#ffd23f',
                                                    boxShadow:
                                                        '0 4px 0 #c79f00',
                                                    padding: '13px 26px',
                                                    font: "800 15px 'Baloo 2'",
                                                    color: '#2a2f6b',
                                                    border: 0,
                                                }}
                                            >
                                                Next
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    );
                })()}

            {screen === 'cat' && cat && (
                <div style={S.shell}>
                    <div
                        style={{
                            ...S.screen,
                            background:
                                'linear-gradient(168deg,#f6f9ff 0%,#eef4fd 46%,#f7f3ff 100%)',
                        }}
                    >
                        <WaveDefs />
                        <div
                            style={
                                {
                                    flex: 'none',
                                    padding: '30px 18px 62px',
                                    position: 'relative',
                                    background: cat.bg,
                                    clipPath: 'url(#hdrWave)',
                                    WebkitClipPath: 'url(#hdrWave)',
                                } as CSSProperties
                            }
                        >
                            <div
                                style={{
                                    position: 'absolute',
                                    right: -30,
                                    top: -40,
                                    width: 150,
                                    height: 150,
                                    borderRadius: '50%',
                                    background: 'rgba(255,255,255,.1)',
                                }}
                            />
                            <div
                                style={{
                                    position: 'absolute',
                                    left: -40,
                                    top: 24,
                                    width: 110,
                                    height: 110,
                                    borderRadius: '50%',
                                    background: 'rgba(255,255,255,.08)',
                                }}
                            />
                            <div
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 12,
                                    position: 'relative',
                                    zIndex: 1,
                                }}
                            >
                                <button
                                    aria-label="Back"
                                    onClick={goBack}
                                    style={{
                                        ...S.back,
                                        background: 'rgba(255,255,255,.9)',
                                    }}
                                >
                                    <span style={S.chev} />
                                </button>
                                <div style={{ flex: 1, minWidth: 0 }}>
                                    <div
                                        style={{
                                            font: "800 23px/1.05 'Baloo 2'",
                                            color: '#fff',
                                            textShadow:
                                                '0 3px 0 rgba(0,0,0,.18)',
                                        }}
                                    >
                                        {cat.title}
                                    </div>
                                    <div
                                        style={{
                                            font: "800 12px 'Nunito'",
                                            color: 'rgba(255,255,255,.9)',
                                            marginTop: 3,
                                        }}
                                    >
                                        {cat.sub}
                                    </div>
                                </div>
                                <div
                                    style={{
                                        font: "800 12px 'Nunito'",
                                        color: '#fff',
                                        background: 'rgba(0,0,0,.18)',
                                        padding: '6px 11px',
                                        borderRadius: 999,
                                    }}
                                >
                                    {list.length} cards
                                </div>
                            </div>
                            <HeaderCrest />
                        </div>
                        <div style={{ ...S.scroll, padding: '8px 16px 26px' }}>
                            <div
                                style={{
                                    display: 'grid',
                                    gridTemplateColumns: 'repeat(4,1fr)',
                                    gap: 10,
                                }}
                            >
                                {tiles.map((t) => (
                                    <button
                                        key={t.i}
                                        onClick={() => {
                                            pushScreen({ screen: 'letter', catId, idx: t.i });
                                            say(t.name, cat.lang);
                                        }}
                                        style={{
                                            cursor: 'pointer',
                                            borderRadius: 18,
                                            background: '#fff',
                                            boxShadow:
                                                '0 4px 0 rgba(30,70,120,.12)',
                                            padding: '8px 4px 6px',
                                            textAlign: 'center',
                                            border: 0,
                                        }}
                                    >
                                        <div
                                            style={{
                                                font: "700 26px/1.1 'Baloo Da 2','Noto Naskh Arabic','Baloo 2'",
                                                color: t.color,
                                            }}
                                        >
                                            {t.ch}
                                        </div>
                                        <div
                                            style={{
                                                font: "800 9px 'Nunito'",
                                                color: '#7d93ad',
                                                marginTop: 4,
                                                overflow: 'hidden',
                                                textOverflow: 'ellipsis',
                                                whiteSpace: 'nowrap',
                                            }}
                                        >
                                            {t.name}
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {screen === 'letter' && cat && (
                <div style={S.shell}>
                    <div style={{ ...S.screen, background: cat.soft }}>
                        <div
                            style={{
                                flex: 'none',
                                display: 'flex',
                                alignItems: 'center',
                                gap: 12,
                                padding: '18px 18px 6px',
                            }}
                        >
                            <button
                                aria-label="Back"
                                onClick={goBack}
                                style={S.back}
                            >
                                <span style={S.chev} />
                            </button>
                            <div
                                style={{
                                    flex: 1,
                                    font: "800 15px 'Baloo 2'",
                                    color: '#2c3e58',
                                }}
                            >
                                {cat.title}
                            </div>
                            <div
                                style={{
                                    font: "800 12px 'Nunito'",
                                    color: '#5c7796',
                                }}
                            >
                                {idx + 1} / {list.length}
                            </div>
                        </div>

                        <div
                            style={{
                                flex: 1,
                                minHeight: 0,
                                display: 'flex',
                                flexDirection: 'column',
                                alignItems: 'center',
                                justifyContent: 'center',
                                gap: 18,
                                padding: '0 22px',
                            }}
                        >
                            <div
                                key={`${cat.id}-${idx}`}
                                style={{
                                    width: 250,
                                    height: 250,
                                    borderRadius: 60,
                                    background: '#fff',
                                    boxShadow: '0 12px 0 rgba(30,70,120,.1)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                }}
                            >
                                <span
                                    style={{
                                        font: "700 150px/1 'Baloo Da 2','Noto Naskh Arabic','Baloo 2'",
                                        color: PAL[idx % PAL.length],
                                        textShadow: '0 8px 12px rgba(0,0,0,.1)',
                                    }}
                                >
                                    {cur.ch}
                                </span>
                            </div>
                            <div style={{ textAlign: 'center' }}>
                                <div
                                    style={{
                                        font: "800 30px/1 'Baloo 2'",
                                        color: '#173d6b',
                                    }}
                                >
                                    {cur.name}
                                </div>
                                <div
                                    style={{
                                        font: "800 15px 'Nunito'",
                                        color: '#4a6d94',
                                        marginTop: 6,
                                    }}
                                >
                                    {cur.word}
                                </div>
                            </div>
                            <button
                                onClick={() => say(cur.name, cat.lang)}
                                style={{
                                    cursor: 'pointer',
                                    borderRadius: 999,
                                    background: '#ffb400',
                                    boxShadow: '0 5px 0 #c98700',
                                    padding: '13px 30px',
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 10,
                                    border: 0,
                                }}
                            >
                                <span
                                    style={{
                                        display: 'block',
                                        width: 14,
                                        height: 14,
                                        background: '#fff',
                                        clipPath:
                                            'polygon(0 30%,45% 30%,100% 0,100% 100%,45% 70%,0 70%)',
                                    }}
                                />
                                <span
                                    style={{
                                        font: "800 16px 'Baloo 2'",
                                        color: '#fff',
                                    }}
                                >
                                    Tap to hear
                                </span>
                            </button>
                        </div>

                        <div
                            style={{
                                flex: 'none',
                                display: 'flex',
                                gap: 12,
                                padding: '0 22px 30px',
                            }}
                        >
                            <button
                                onClick={() => step(-1)}
                                style={{
                                    flex: 1,
                                    cursor: 'pointer',
                                    borderRadius: 20,
                                    background: '#fff',
                                    boxShadow: '0 4px 0 rgba(30,70,120,.14)',
                                    padding: '14px 0',
                                    font: "800 15px 'Baloo 2'",
                                    color: '#2c3e58',
                                    border: 0,
                                }}
                            >
                                Back
                            </button>
                            <button
                                onClick={() => step(1)}
                                style={{
                                    flex: 1.6,
                                    cursor: 'pointer',
                                    borderRadius: 20,
                                    background: cat.bg,
                                    boxShadow: '0 4px 0 rgba(0,0,0,.2)',
                                    padding: '14px 0',
                                    font: "800 15px 'Baloo 2'",
                                    color: '#fff',
                                    border: 0,
                                }}
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
