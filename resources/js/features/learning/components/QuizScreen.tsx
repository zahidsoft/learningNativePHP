import type { CSSProperties } from "react";
import { S } from "@/features/learning/lib/styles";
import type { Question } from "@/features/learning/types";

export function QuizScreen({
    score,
    qNum,
    q,
    picked,
    onPick,
    onNext,
    onBack,
}: {
    score: number;
    qNum: number;
    q: Question;
    picked: string | null;
    onPick: (name: string) => void;
    onNext: () => void;
    onBack: () => void;
}) {
    const correct = !!picked && picked === q.answer.name;

    const optStyle = (name: string): CSSProperties => {
        let bg = "rgba(255,255,255,.94)";
        let fg = "#2a2f6b";
        let shadow = "rgba(0,0,0,.22)";
        if (picked) {
            if (name === q.answer.name) {
                bg = "#3ec46a";
                fg = "#fff";
                shadow = "#23874a";
            } else if (name === picked) {
                bg = "#ef476f";
                fg = "#fff";
                shadow = "#a52948";
            } else {
                bg = "rgba(255,255,255,.5)";
                fg = "#4a4a7a";
            }
        }
        return {
            cursor: "pointer",
            textAlign: "center",
            borderRadius: 20,
            padding: "15px 8px",
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
                        "linear-gradient(170deg,#232a63 0%,#3b3591 55%,#5a3fbe 100%)",
                }}
            >
                <div
                    style={{
                        flex: "none",
                        display: "flex",
                        alignItems: "center",
                        gap: 12,
                        padding: "22px 18px 8px",
                    }}
                >
                    <button
                        aria-label="Back"
                        onClick={onBack}
                        style={{
                            ...S.back,
                            background: "rgba(255,255,255,.16)",
                            boxShadow: "none",
                        }}
                    >
                        <span
                            style={{
                                ...S.chev,
                                borderLeftColor: "#fff",
                                borderBottomColor: "#fff",
                            }}
                        />
                    </button>
                    <div
                        style={{
                            flex: 1,
                            font: "800 18px 'Baloo 2'",
                            color: "#fff",
                        }}
                    >
                        Quiz Time
                    </div>
                    <div
                        style={{
                            font: "800 12px 'Nunito'",
                            color: "#ffd23f",
                            background: "rgba(0,0,0,.22)",
                            padding: "7px 12px",
                            borderRadius: 999,
                        }}
                    >
                        Score {score}
                    </div>
                </div>

                <div
                    style={{
                        flex: "none",
                        padding: "10px 20px 0",
                    }}
                >
                    <div
                        style={{
                            height: 8,
                            borderRadius: 999,
                            background: "rgba(255,255,255,.16)",
                            overflow: "hidden",
                        }}
                    >
                        <div
                            style={{
                                height: "100%",
                                borderRadius: 999,
                                background: "#ffd23f",
                                width: `${Math.round((qNum / 10) * 100)}%`,
                            }}
                        />
                    </div>
                    <div
                        style={{
                            font: "800 11px 'Nunito'",
                            color: "#c7c2ff",
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
                        display: "flex",
                        flexDirection: "column",
                        justifyContent: "center",
                        gap: 22,
                        padding: "0 20px",
                    }}
                >
                    <div
                        style={{
                            textAlign: "center",
                            font: "800 15px 'Nunito'",
                            color: "#cfc9ff",
                        }}
                    >
                        Which one is this letter?
                    </div>
                    <div
                        key={qNum}
                        style={{
                            alignSelf: "center",
                            width: 190,
                            height: 190,
                            borderRadius: 48,
                            background: "rgba(255,255,255,.97)",
                            boxShadow: "0 12px 0 rgba(0,0,0,.22)",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                        }}
                    >
                        <span
                            style={{
                                font: "700 110px/1 'Baloo Da 2','Noto Naskh Arabic','Baloo 2'",
                                color: "#3b3591",
                            }}
                        >
                            {q.answer.ch}
                        </span>
                    </div>
                    <div
                        style={{
                            display: "grid",
                            gridTemplateColumns: "1fr 1fr",
                            gap: 12,
                        }}
                    >
                        {q.opts.map((o) => (
                            <button
                                key={o.name}
                                onClick={() => onPick(o.name)}
                                style={optStyle(o.name)}
                            >
                                {o.name}
                            </button>
                        ))}
                    </div>
                </div>

                <div
                    style={{
                        flex: "none",
                        padding: "0 20px 30px",
                        minHeight: 78,
                        display: "flex",
                        alignItems: "center",
                    }}
                >
                    {picked && (
                        <div
                            style={{
                                flex: 1,
                                display: "flex",
                                alignItems: "center",
                                gap: 12,
                            }}
                        >
                            <div
                                style={{
                                    flex: 1,
                                    font: "800 15px 'Baloo 2'",
                                    color: correct ? "#8df0ae" : "#ffb3c6",
                                }}
                            >
                                {correct
                                    ? "Correct! Well done"
                                    : `Oops — it was ${q.answer.name}`}
                            </div>
                            <button
                                onClick={onNext}
                                style={{
                                    cursor: "pointer",
                                    borderRadius: 18,
                                    background: "#ffd23f",
                                    boxShadow: "0 4px 0 #c79f00",
                                    padding: "13px 26px",
                                    font: "800 15px 'Baloo 2'",
                                    color: "#2a2f6b",
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
}
