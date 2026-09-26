export type Letter = { ch: string; name: string; word?: string };

export type CatId = "english" | "arabic" | "numbers" | "consonants" | "vowels";

export type Cat = {
    id: CatId;
    title: string;
    sub: string;
    bg: string;
    soft: string;
    lang: string;
    letters: Letter[];
};

export type Screen = "home" | "cat" | "letter" | "quiz";

// A screen transition pushed onto browser history, so the phone's hardware
// back button (which the native shell maps to WebView.goBack() when history
// exists, or exits the app otherwise) steps back through our screens the
// same way the on-screen back arrow does, instead of quitting the app from
// any depth.
export type NavState = {
    screen: Screen;
    catId: CatId | null;
    idx: number;
};

export type Question = {
    answer: Letter & { lang: string };
    opts: (Letter & { lang: string })[];
};
