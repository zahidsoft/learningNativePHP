import { useRef } from "react";
import type { TouchEvent } from "react";
import { PAL } from "@/features/learning/data/categories";
import { LETTER_PHONEMES } from "@/features/learning/data/phonemes";
import { WORDS_BY_LETTER } from "@/features/learning/data/words.generated";
import { S } from "@/features/learning/lib/styles";
import type { Cat } from "@/features/learning/types";
import { playAudioFile } from "@/lib/audio";

export function LetterScreen({
    cat,
    idx,
    onBack,
    onStep,
    onHear,
}: {
    cat: Cat;
    idx: number;
    onBack: () => void;
    onStep: (d: number) => void;
    onHear: () => void;
}) {
    const list = cat.letters;
    const cur = list[idx] ?? { ch: "", name: "", word: "" };
    const words = WORDS_BY_LETTER[cur.ch] ?? [];
    const phonemes = LETTER_PHONEMES[cur.ch] ?? [];

    // Swipe left -> next letter, swipe right -> previous letter. A ref (not
    // state) so tracking a touch doesn't trigger re-renders.
    const swipeStart = useRef<{ x: number; y: number } | null>(null);
    const onTouchStart = (e: TouchEvent) => {
        const t = e.touches[0];
        swipeStart.current = { x: t.clientX, y: t.clientY };
    };
    const onTouchEnd = (e: TouchEvent) => {
        const start = swipeStart.current;
        swipeStart.current = null;
        if (!start) return;
        const t = e.changedTouches[0];
        const dx = t.clientX - start.x;
        const dy = t.clientY - start.y;
        // Require a deliberate horizontal drag, not a tap or a vertical/diagonal one.
        if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy) * 1.5) return;
        onStep(dx < 0 ? 1 : -1);
    };

    return (
        <div style={S.shell}>
            <div
                style={{ ...S.screen, background: cat.soft }}
                onTouchStart={onTouchStart}
                onTouchEnd={onTouchEnd}
            >
                <div
                    style={{
                        flex: "none",
                        display: "flex",
                        alignItems: "center",
                        gap: 12,
                        padding: "18px 18px 6px",
                    }}
                >
                    <button aria-label="Back" onClick={onBack} style={S.back}>
                        <span style={S.chev} />
                    </button>
                    <div
                        style={{
                            flex: 1,
                            font: "800 15px 'Baloo 2'",
                            color: "#2c3e58",
                        }}
                    >
                        {cat.title}
                    </div>
                    <div
                        style={{
                            font: "800 12px 'Nunito'",
                            color: "#5c7796",
                        }}
                    >
                        {idx + 1} / {list.length}
                    </div>
                </div>

                <div
                    style={{
                        flex: 1,
                        minHeight: 0,
                        display: "flex",
                        flexDirection: "column",
                        alignItems: "center",
                        justifyContent: "center",
                        gap: 18,
                        padding: "0 22px",
                    }}
                >
                    <button
                        key={`${cat.id}-${idx}`}
                        onClick={onHear}
                        aria-label={`Hear ${cur.name}`}
                        style={{
                            width: 250,
                            height: 250,
                            borderRadius: 60,
                            background: "#fff",
                            boxShadow: "0 12px 0 rgba(30,70,120,.1)",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            border: 0,
                            padding: 0,
                            cursor: "pointer",
                        }}
                    >
                        <span
                            style={{
                                font: "700 150px/1 'Baloo Da 2','Noto Naskh Arabic','Baloo 2'",
                                color: PAL[idx % PAL.length],
                                textShadow: "0 8px 12px rgba(0,0,0,.1)",
                            }}
                        >
                            {cur.ch}
                        </span>
                    </button>
                    <div style={{ textAlign: "center" }}>
                        <div
                            style={{
                                font: "800 30px/1 'Baloo 2'",
                                color: "#173d6b",
                            }}
                        >
                            {cur.name}
                        </div>
                        <div
                            style={{
                                font: "800 15px 'Nunito'",
                                color: "#4a6d94",
                                marginTop: 6,
                            }}
                        >
                            {cur.word}
                        </div>
                    </div>
                    <button
                        onClick={onHear}
                        style={{
                            cursor: "pointer",
                            borderRadius: 999,
                            background: "#ffb400",
                            boxShadow: "0 5px 0 #c98700",
                            padding: "13px 30px",
                            display: "flex",
                            alignItems: "center",
                            gap: 10,
                            border: 0,
                        }}
                    >
                        <span
                            style={{
                                display: "block",
                                width: 14,
                                height: 14,
                                background: "#fff",
                                clipPath:
                                    "polygon(0 30%,45% 30%,100% 0,100% 100%,45% 70%,0 70%)",
                            }}
                        />
                        <span
                            style={{
                                font: "800 16px 'Baloo 2'",
                                color: "#fff",
                            }}
                        >
                            Tap to hear
                        </span>
                    </button>
                    {phonemes.length > 0 && (
                        <div style={{ display: "flex", gap: 10 }}>
                            {phonemes.map((ph) => (
                                <div
                                    key={ph.symbol}
                                    style={{
                                        display: "flex",
                                        flexDirection: "column",
                                        alignItems: "center",
                                        gap: 4,
                                        borderRadius: 16,
                                        background: "#fff",
                                        boxShadow:
                                            "0 3px 0 rgba(30,70,120,.12)",
                                        padding: "8px 14px",
                                    }}
                                >
                                    <button
                                        onClick={() =>
                                            playAudioFile(
                                                ph.isolation,
                                            ).catch(() => {})
                                        }
                                        aria-label={`Hear the /${ph.symbol}/ sound`}
                                        style={{
                                            border: 0,
                                            background: "transparent",
                                            cursor: "pointer",
                                            padding: 0,
                                            font: "700 20px 'Baloo Da 2'",
                                            color: cat.bg,
                                        }}
                                    >
                                        /{ph.symbol}/
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                    {words.length > 0 && (
                        <div
                            style={{
                                display: "flex",
                                gap: 10,
                                width: "100%",
                                overflowX: "auto",
                                padding: "2px 2px 6px",
                            }}
                        >
                            {words.map((w) => (
                                <button
                                    key={w.text}
                                    onClick={() =>
                                        playAudioFile(w.audio).catch(
                                            () => {},
                                        )
                                    }
                                    aria-label={`Hear ${w.text}`}
                                    style={{
                                        flex: "none",
                                        width: 68,
                                        display: "flex",
                                        flexDirection: "column",
                                        alignItems: "center",
                                        gap: 4,
                                        cursor: "pointer",
                                        border: 0,
                                        background: "transparent",
                                        padding: 0,
                                    }}
                                >
                                    <img
                                        src={w.image}
                                        alt={w.text}
                                        style={{
                                            width: 56,
                                            height: 56,
                                            borderRadius: 16,
                                            objectFit: "cover",
                                            background: "#fff",
                                            boxShadow:
                                                "0 3px 0 rgba(30,70,120,.12)",
                                        }}
                                    />
                                    <span
                                        style={{
                                            font: "800 10.5px 'Nunito'",
                                            color: "#4a6d94",
                                        }}
                                    >
                                        {w.text}
                                    </span>
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                <div
                    style={{
                        flex: "none",
                        display: "flex",
                        gap: 12,
                        padding: "0 22px 30px",
                    }}
                >
                    <button
                        onClick={() => onStep(-1)}
                        style={{
                            flex: 1,
                            cursor: "pointer",
                            borderRadius: 20,
                            background: "#fff",
                            boxShadow: "0 4px 0 rgba(30,70,120,.14)",
                            padding: "14px 0",
                            font: "800 15px 'Baloo 2'",
                            color: "#2c3e58",
                            border: 0,
                        }}
                    >
                        Back
                    </button>
                    <button
                        onClick={() => onStep(1)}
                        style={{
                            flex: 1.6,
                            cursor: "pointer",
                            borderRadius: 20,
                            background: cat.bg,
                            boxShadow: "0 4px 0 rgba(0,0,0,.2)",
                            padding: "14px 0",
                            font: "800 15px 'Baloo 2'",
                            color: "#fff",
                            border: 0,
                        }}
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    );
}
