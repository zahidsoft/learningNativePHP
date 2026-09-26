import { useState } from "react";
import { CATS } from "@/features/learning/data/categories";
import { makeQuestion } from "@/features/learning/data/quiz";
import { useLetterSound } from "@/features/learning/hooks/use-letter-sound";
import { useScreenHistory } from "@/features/learning/hooks/use-screen-history";
import { CategoryScreen } from "@/features/learning/components/CategoryScreen";
import { HomeScreen } from "@/features/learning/components/HomeScreen";
import { LetterScreen } from "@/features/learning/components/LetterScreen";
import { QuizScreen } from "@/features/learning/components/QuizScreen";
import type { CatId, NavState, Screen } from "@/features/learning/types";

export function LearningApp() {
    const [screen, setScreen] = useState<Screen>("home");
    const [catId, setCatId] = useState<CatId | null>(null);
    const [idx, setIdx] = useState(0);
    const [q, setQ] = useState(makeQuestion);
    const [qNum, setQNum] = useState(1);
    const [score, setScore] = useState(0);
    const [picked, setPicked] = useState<string | null>(null);

    const { speakLetter, speakQuizFeedback } = useLetterSound();

    const { pushScreen, goBack } = useScreenHistory(
        (s: NavState) => {
            setScreen(s.screen);
            setCatId(s.catId);
            setIdx(s.idx);
        },
        { screen: "home", catId: null, idx: 0 },
    );

    const cat = catId ? CATS[catId] : null;
    const list = cat ? cat.letters : [];

    const openCategory = (id: CatId) => {
        pushScreen({ screen: "cat", catId: id, idx: 0 });
    };

    const openQuiz = () => {
        setQ(makeQuestion());
        setQNum(1);
        setScore(0);
        setPicked(null);
        pushScreen({ screen: "quiz", catId, idx });
    };

    const selectLetter = (i: number) => {
        pushScreen({ screen: "letter", catId, idx: i });
        if (cat) speakLetter(cat.id, list[i].name, cat.lang);
    };

    const step = (d: number) => {
        const n = list.length;
        if (!n || !cat) return;
        const i = (idx + d + n) % n;
        setIdx(i);
        speakLetter(cat.id, list[i].name, cat.lang);
    };

    const hearCurrent = () => {
        const cur = list[idx];
        if (cat && cur) speakLetter(cat.id, cur.name, cat.lang);
    };

    const pick = (name: string) => {
        if (picked) return;
        const ok = name === q.answer.name;
        speakQuizFeedback(ok);
        setPicked(name);
        if (ok) setScore((v) => v + 1);
    };

    const nextQuestion = () => {
        if (qNum >= 10) {
            goBack();
            setQNum(1);
            setScore(0);
        } else {
            setQNum((v) => v + 1);
        }
        setQ(makeQuestion());
        setPicked(null);
    };

    if (screen === "quiz") {
        return (
            <QuizScreen
                score={score}
                qNum={qNum}
                q={q}
                picked={picked}
                onPick={pick}
                onNext={nextQuestion}
                onBack={goBack}
            />
        );
    }

    if (screen === "cat" && cat) {
        return (
            <CategoryScreen
                cat={cat}
                onBack={goBack}
                onSelectLetter={selectLetter}
            />
        );
    }

    if (screen === "letter" && cat) {
        return (
            <LetterScreen
                cat={cat}
                idx={idx}
                onBack={goBack}
                onStep={step}
                onHear={hearCurrent}
            />
        );
    }

    return <HomeScreen onOpenCategory={openCategory} onOpenQuiz={openQuiz} />;
}
