import { useMemo } from "react";
import type { CSSProperties } from "react";
import { PAL } from "@/features/learning/data/categories";
import { S } from "@/features/learning/lib/styles";
import { HeaderCrest } from "@/features/learning/components/HeaderCrest";
import { WaveDefs } from "@/features/learning/components/WaveDefs";
import type { Cat } from "@/features/learning/types";

export function CategoryScreen({
    cat,
    onBack,
    onSelectLetter,
}: {
    cat: Cat;
    onBack: () => void;
    onSelectLetter: (idx: number) => void;
}) {
    const tiles = useMemo(
        () => cat.letters.map((l, i) => ({ ...l, color: PAL[i % PAL.length], i })),
        [cat.letters],
    );

    return (
        <div style={S.shell}>
            <div
                style={{
                    ...S.screen,
                    background:
                        "linear-gradient(168deg,#f6f9ff 0%,#eef4fd 46%,#f7f3ff 100%)",
                }}
            >
                <WaveDefs />
                <div
                    style={
                        {
                            flex: "none",
                            padding: "30px 18px 62px",
                            position: "relative",
                            background: cat.bg,
                            clipPath: "url(#hdrWave)",
                            WebkitClipPath: "url(#hdrWave)",
                        } as CSSProperties
                    }
                >
                    <div
                        style={{
                            position: "absolute",
                            right: -30,
                            top: -40,
                            width: 150,
                            height: 150,
                            borderRadius: "50%",
                            background: "rgba(255,255,255,.1)",
                        }}
                    />
                    <div
                        style={{
                            position: "absolute",
                            left: -40,
                            top: 24,
                            width: 110,
                            height: 110,
                            borderRadius: "50%",
                            background: "rgba(255,255,255,.08)",
                        }}
                    />
                    <div
                        style={{
                            display: "flex",
                            alignItems: "center",
                            gap: 12,
                            position: "relative",
                            zIndex: 1,
                        }}
                    >
                        <button
                            aria-label="Back"
                            onClick={onBack}
                            style={{
                                ...S.back,
                                background: "rgba(255,255,255,.9)",
                            }}
                        >
                            <span style={S.chev} />
                        </button>
                        <div style={{ flex: 1, minWidth: 0 }}>
                            <div
                                style={{
                                    font: "800 23px/1.05 'Baloo 2'",
                                    color: "#fff",
                                    textShadow: "0 3px 0 rgba(0,0,0,.18)",
                                }}
                            >
                                {cat.title}
                            </div>
                            <div
                                style={{
                                    font: "800 12px 'Nunito'",
                                    color: "rgba(255,255,255,.9)",
                                    marginTop: 3,
                                }}
                            >
                                {cat.sub}
                            </div>
                        </div>
                        <div
                            style={{
                                font: "800 12px 'Nunito'",
                                color: "#fff",
                                background: "rgba(0,0,0,.18)",
                                padding: "6px 11px",
                                borderRadius: 999,
                            }}
                        >
                            {cat.letters.length} cards
                        </div>
                    </div>
                    <HeaderCrest />
                </div>
                <div style={{ ...S.scroll, padding: "8px 16px 26px" }}>
                    <div
                        style={{
                            display: "grid",
                            gridTemplateColumns: "repeat(4,1fr)",
                            gap: 10,
                        }}
                    >
                        {tiles.map((t) => (
                            <button
                                key={t.i}
                                onClick={() => onSelectLetter(t.i)}
                                style={{
                                    cursor: "pointer",
                                    borderRadius: 18,
                                    background: "#fff",
                                    boxShadow: "0 4px 0 rgba(30,70,120,.12)",
                                    padding: "8px 4px 6px",
                                    textAlign: "center",
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
                                        color: "#7d93ad",
                                        marginTop: 4,
                                        overflow: "hidden",
                                        textOverflow: "ellipsis",
                                        whiteSpace: "nowrap",
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
    );
}
