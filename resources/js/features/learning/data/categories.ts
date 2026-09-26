import type { Cat, CatId, Letter } from "@/features/learning/types";

export const PAL = [
    "#ee2f5a",
    "#1f7ae0",
    "#f9a11b",
    "#3aa93a",
    "#8b3ff0",
    "#d62b74",
];

const p = (s: string, fallback = ""): Letter[] =>
    s.split("|").map((row) => {
        const b = row.split(":");
        return { ch: b[0], name: b[1], word: b[2] || fallback };
    });

export const CATS: Record<CatId, Cat> = {
    english: {
        id: "english",
        title: "English Letters",
        sub: "abc · A to Z",
        bg: "#1f7ae0",
        soft: "#e8f3ff",
        lang: "en-US",
        letters: p(
            "A:Ay:Apple|B:Bee:Ball|C:See:Cat|D:Dee:Duck|E:Ee:Egg|F:Ef:Fish|G:Jee:Goat|H:Aitch:Hat|I:Eye:Ice cream|J:Jay:Jug|K:Kay:Kite|L:El:Lion|M:Em:Moon|N:En:Nest|O:Oh:Owl|P:Pee:Pencil|Q:Queue:Queen|R:Ar:Rain|S:Es:Sun|T:Tee:Tree|U:You:Umbrella|V:Vee:Van|W:Double-u:Water|X:Ex:Box|Y:Wy:Yarn|Z:Zed:Zebra",
        ),
    },
    arabic: {
        id: "arabic",
        title: "Arabic Letters",
        sub: "Alif Ba · 28 letters",
        bg: "#1f8b3a",
        soft: "#eafaea",
        lang: "ar-SA",
        letters: p(
            "ا:Alif|ب:Ba|ت:Ta|ث:Tha|ج:Jim|ح:Ha|خ:Kha|د:Dal|ذ:Dhal|ر:Ra|ز:Zay|س:Sin|ش:Shin|ص:Sad|ض:Dad|ط:Taa|ظ:Zaa|ع:Ain|غ:Ghain|ف:Fa|ق:Qaf|ك:Kaf|ل:Lam|م:Mim|ن:Nun|ه:Haa|و:Waw|ي:Ya",
            "Arabic alphabet",
        ),
    },
    numbers: {
        id: "numbers",
        title: "Numbers",
        sub: "1, 2, 3, 4 · 1 to 20",
        bg: "#d69200",
        soft: "#fff6e0",
        lang: "en-US",
        letters: p(
            "1:One:১|2:Two:২|3:Three:৩|4:Four:৪|5:Five:৫|6:Six:৬|7:Seven:৭|8:Eight:৮|9:Nine:৯|10:Ten:১০|11:Eleven:১১|12:Twelve:১২|13:Thirteen:১৩|14:Fourteen:১৪|15:Fifteen:১৫|16:Sixteen:১৬|17:Seventeen:১৭|18:Eighteen:১৮|19:Nineteen:১৯|20:Twenty:২০",
        ),
    },
    consonants: {
        id: "consonants",
        title: "Bangla Consonants",
        sub: "ক, খ, গ · ব্যঞ্জনবর্ণ",
        bg: "#6b2fd6",
        soft: "#f3edff",
        lang: "bn-BD",
        letters: p(
            "ক:ko|খ:kho|গ:go|ঘ:gho|ঙ:ungo|চ:cho|ছ:chho|জ:borgio jo|ঝ:jho|ঞ:ingo|ট:to|ঠ:tho|ড:do|ঢ:dho|ণ:murdhonno no|ত:to|থ:tho|দ:do|ধ:dho|ন:donto no|প:po|ফ:pho|ব:bo|ভ:bho|ম:mo|য:ontosto jo|র:ro|ল:lo|শ:talobbo sho|ষ:murdhonno sho|স:donto so|হ:ho",
            "ব্যঞ্জনবর্ণ",
        ),
    },
    vowels: {
        id: "vowels",
        title: "Bangla Vowels",
        sub: "অ, আ ই · স্বরবর্ণ",
        bg: "#d62b74",
        soft: "#fff0f6",
        lang: "bn-BD",
        letters: p(
            "অ:o|আ:a|ই:i|ঈ:dirgho i|উ:u|ঊ:dirgho u|ঋ:ri|এ:e|ঐ:oi|ও:o|ঔ:ou",
            "স্বরবর্ণ",
        ),
    },
};

export const ALL: (Letter & { lang: string })[] = (
    Object.keys(CATS) as CatId[]
).flatMap((k) => CATS[k].letters.map((l) => ({ ...l, lang: CATS[k].lang })));
