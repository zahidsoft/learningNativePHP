import { S, STICKER, card, glyph, pill } from "@/features/learning/lib/styles";
import { BookIcon } from "@/features/learning/components/BookIcon";
import { HeaderCrest } from "@/features/learning/components/HeaderCrest";
import { WaveDefs } from "@/features/learning/components/WaveDefs";
import type { CSSProperties } from "react";
import type { CatId } from "@/features/learning/types";

export function HomeScreen({
    onOpenCategory,
    onOpenQuiz,
}: {
    onOpenCategory: (id: CatId) => void;
    onOpenQuiz: () => void;
}) {
    return (
        <div style={S.shell}>
            <div style={S.sky}>
                <div style={S.blooms} />
                <div style={S.dots} />
                <div style={S.fade} />
                <WaveDefs />
                <div
                    style={
                        {
                            position: "relative",
                            padding: "34px 18px 74px",
                            background:
                                "linear-gradient(180deg,#1478e6 0%,#2b9bf4 55%,#57b6fa 100%)",
                            flex: "none",
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
                            width: 160,
                            height: 160,
                            borderRadius: "50%",
                            background: "rgba(255,255,255,.08)",
                        }}
                    />
                    <div
                        style={{
                            position: "absolute",
                            left: -40,
                            top: 30,
                            width: 120,
                            height: 120,
                            borderRadius: "50%",
                            background: "rgba(255,255,255,.07)",
                        }}
                    />
                    <div
                        style={{
                            position: "relative",
                            display: "flex",
                            alignItems: "center",
                            gap: 12,
                        }}
                    >
                        <div
                            style={{
                                ...S.ph,
                                width: 52,
                                height: 52,
                                flex: "none",
                                borderRadius: 14,
                                border: "3px solid #2f7d32",
                                color: "#2f7d32",
                                background:
                                    "repeating-linear-gradient(135deg,#fff,#fff 5px,#e6f2ff 5px,#e6f2ff 10px)",
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
                                    color: "#fff",
                                    letterSpacing: "-.5px",
                                    textShadow: "0 3px 0 rgba(12,74,130,.35)",
                                }}
                            >
                                Learn{" "}
                                <span style={{ color: "#ffd23f" }}>
                                    &amp;
                                </span>{" "}
                                Grow
                            </div>
                            <div
                                style={{
                                    font: "700 13px 'Nunito'",
                                    color: "#eaf6ff",
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
                                flex: "none",
                                borderRadius: "50%",
                                background: "#fff",
                                boxShadow: "0 4px 0 rgba(12,74,130,.25)",
                                display: "flex",
                                alignItems: "center",
                                justifyContent: "center",
                                border: 0,
                                cursor: "pointer",
                            }}
                        >
                            <span
                                style={{
                                    position: "relative",
                                    width: 26,
                                    height: 26,
                                    borderRadius: "50%",
                                    background:
                                        "repeating-conic-gradient(from 11.25deg,#2c3e58 0 22.5deg,rgba(0,0,0,0) 22.5deg 45deg)",
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "center",
                                }}
                            >
                                <span
                                    style={{
                                        width: 20,
                                        height: 20,
                                        borderRadius: "50%",
                                        background: "#2c3e58",
                                        display: "flex",
                                        alignItems: "center",
                                        justifyContent: "center",
                                    }}
                                >
                                    <span
                                        style={{
                                            width: 7,
                                            height: 7,
                                            borderRadius: "50%",
                                            background: "#fff",
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
                        position: "relative",
                        padding: "8px 16px 18px",
                    }}
                >
                    <div
                        style={{
                            display: "flex",
                            alignItems: "center",
                            gap: 10,
                            padding: "2px 0 14px",
                        }}
                    >
                        <div
                            style={{
                                ...S.ph,
                                width: 66,
                                height: 66,
                                flex: "none",
                                borderRadius: 18,
                                color: "#4a7ba8",
                                background:
                                    "repeating-linear-gradient(45deg,#fff,#fff 5px,#dceeff 5px,#dceeff 10px)",
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
                                    color: "#173d6b",
                                }}
                            >
                                Welcome00 1!
                            </div>
                            <div
                                style={{
                                    font: "700 13.5px 'Nunito'",
                                    color: "#3a6390",
                                    marginTop: 4,
                                }}
                            >
                                Choose a category okqqto start learning
                            </div>
                        </div>
                    </div>

                    <div style={S.grid}>
                        <button
                            onClick={() => onOpenCategory("english")}
                            style={card(
                                "#d6ecff",
                                "#a9d8fb",
                                "rgba(31,122,224,.18)",
                            )}
                        >
                            <div
                                style={{
                                    position: "absolute",
                                    inset: 0,
                                    font: "800 34px 'Baloo 2'",
                                    color: "rgba(31,122,224,.13)",
                                    padding: "6px 10px",
                                    display: "flex",
                                    justifyContent: "space-between",
                                }}
                            >
                                A<span>B</span>
                            </div>
                            <div style={S.glyphRow}>
                                <span style={glyph("#ee2f5a", 50, "'Baloo 2'")}>
                                    A
                                </span>
                                <span style={glyph("#1f7ae0", 50, "'Baloo 2'")}>
                                    B
                                </span>
                                <span style={glyph("#f9b21b", 50, "'Baloo 2'")}>
                                    C
                                </span>
                            </div>
                            <div style={pill("#1f7ae0", "rgba(12,60,120,.35)")}>
                                <BookIcon />
                                <div>
                                    <div
                                        style={{
                                            font: "800 17px/1 'Baloo 2'",
                                            color: "#fff",
                                        }}
                                    >
                                        abc
                                    </div>
                                    <div
                                        style={{
                                            font: "800 10.5px 'Nunito'",
                                            color: "#e6f2ff",
                                            marginTop: 2,
                                        }}
                                    >
                                        English Letters
                                    </div>
                                </div>
                            </div>
                        </button>

                        <button
                            onClick={() => onOpenCategory("arabic")}
                            style={card(
                                "#c9f0c2",
                                "#8ddc86",
                                "rgba(45,125,45,.2)",
                            )}
                        >
                            <div
                                style={{
                                    ...S.glyphRow,
                                    direction: "rtl",
                                    font: "700 48px/1 'Noto Naskh Arabic'",
                                    color: "#14682a",
                                    textShadow: STICKER,
                                }}
                            >
                                ا ب ت
                            </div>
                            <div style={pill("#1f8b3a", "rgba(15,70,30,.4)")}>
                                <span style={S.pillIcon}>
                                    <span
                                        style={{
                                            display: "block",
                                            width: 13,
                                            height: 13,
                                            borderRadius: "50% 50% 3px 3px",
                                            background: "#fff",
                                        }}
                                    />
                                </span>
                                <div>
                                    <div
                                        style={{
                                            font: "800 14.5px/1 'Baloo 2'",
                                            color: "#fff",
                                        }}
                                    >
                                        Arabic Letters
                                    </div>
                                    <div
                                        style={{
                                            font: "800 10.5px 'Nunito'",
                                            color: "#e4ffe8",
                                            marginTop: 2,
                                        }}
                                    >
                                        Alif Ba
                                    </div>
                                </div>
                            </div>
                        </button>

                        <button
                            onClick={() => onOpenCategory("numbers")}
                            style={card(
                                "#ffe08a",
                                "#f9c23c",
                                "rgba(190,130,10,.2)",
                            )}
                        >
                            <div
                                style={{
                                    position: "absolute",
                                    inset: 0,
                                    padding: "6px 10px",
                                    display: "flex",
                                    justifyContent: "space-between",
                                    font: "800 30px 'Baloo 2'",
                                    color: "rgba(160,110,10,.16)",
                                }}
                            >
                                5<span>8</span>
                            </div>
                            <div style={S.glyphRow}>
                                <span style={glyph("#ee2f30", 48, "'Baloo 2'")}>
                                    1
                                </span>
                                <span style={glyph("#1f7ae0", 48, "'Baloo 2'")}>
                                    2
                                </span>
                                <span style={glyph("#5fb316", 48, "'Baloo 2'")}>
                                    3
                                </span>
                                <span style={glyph("#8b3ff0", 48, "'Baloo 2'")}>
                                    4
                                </span>
                            </div>
                            <div style={pill("#d69200", "rgba(130,88,0,.4)")}>
                                <span style={S.pillIcon}>
                                    <span
                                        style={{
                                            display: "block",
                                            width: 13,
                                            height: 13,
                                            borderRadius: 3,
                                            background: "#fff",
                                        }}
                                    />
                                </span>
                                <div>
                                    <div
                                        style={{
                                            font: "800 15px/1 'Baloo 2'",
                                            color: "#fff",
                                        }}
                                    >
                                        1, 2, 3, 4
                                    </div>
                                    <div
                                        style={{
                                            font: "800 10.5px 'Nunito'",
                                            color: "#fff5df",
                                            marginTop: 2,
                                        }}
                                    >
                                        Numbers
                                    </div>
                                </div>
                            </div>
                        </button>

                        <button
                            onClick={() => onOpenCategory("consonants")}
                            style={card(
                                "#e2d6ff",
                                "#c3a9fb",
                                "rgba(100,60,190,.2)",
                            )}
                        >
                            <div
                                style={{
                                    position: "absolute",
                                    inset: 0,
                                    padding: "6px 10px",
                                    display: "flex",
                                    justifyContent: "space-between",
                                    font: "700 28px 'Baloo Da 2'",
                                    color: "rgba(90,50,180,.16)",
                                }}
                            >
                                ব<span>স</span>
                            </div>
                            <div style={{ ...S.glyphRow, gap: 6 }}>
                                <span
                                    style={glyph("#ee2f6f", 44, "'Baloo Da 2'")}
                                >
                                    ক
                                </span>
                                <span
                                    style={glyph("#1f7ae0", 44, "'Baloo Da 2'")}
                                >
                                    খ
                                </span>
                                <span
                                    style={glyph("#3aa93a", 44, "'Baloo Da 2'")}
                                >
                                    গ
                                </span>
                            </div>
                            <div style={pill("#6b2fd6", "rgba(55,20,120,.4)")}>
                                <BookIcon />
                                <div>
                                    <div
                                        style={{
                                            font: "700 15px/1 'Baloo Da 2'",
                                            color: "#fff",
                                        }}
                                    >
                                        ক, খ, গ
                                    </div>
                                    <div
                                        style={{
                                            font: "800 10px 'Nunito'",
                                            color: "#f0e7ff",
                                            marginTop: 2,
                                        }}
                                    >
                                        বাংলা ব্যঞ্জনবর্ণ
                                    </div>
                                </div>
                            </div>
                        </button>

                        <button
                            onClick={() => onOpenCategory("vowels")}
                            style={card(
                                "#ffdcec",
                                "#ffb9d6",
                                "rgba(200,60,120,.18)",
                                true,
                            )}
                        >
                            <div
                                style={{
                                    position: "absolute",
                                    inset: 0,
                                    padding: "8px 14px",
                                    display: "flex",
                                    justifyContent: "space-between",
                                    font: "700 30px 'Baloo Da 2'",
                                    color: "rgba(200,50,110,.16)",
                                }}
                            >
                                অ<span>আ</span>
                            </div>
                            <div style={{ ...S.glyphRow, gap: 16 }}>
                                <span
                                    style={glyph("#ee2f5a", 46, "'Baloo Da 2'")}
                                >
                                    অ
                                </span>
                                <span
                                    style={glyph("#1f7ae0", 46, "'Baloo Da 2'")}
                                >
                                    আ
                                </span>
                                <span
                                    style={glyph("#f9a11b", 46, "'Baloo Da 2'")}
                                >
                                    ই
                                </span>
                            </div>
                            <div
                                style={{
                                    position: "relative",
                                    marginTop: 6,
                                    display: "flex",
                                    alignItems: "center",
                                    gap: 10,
                                }}
                            >
                                <div
                                    style={{
                                        ...pill(
                                            "#d62b74",
                                            "rgba(140,20,70,.4)",
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
                                                color: "#fff",
                                            }}
                                        >
                                            অ, আ ই
                                        </div>
                                        <div
                                            style={{
                                                font: "800 10.5px 'Nunito'",
                                                color: "#ffe6f1",
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
                                        flex: "none",
                                        borderRadius: 14,
                                        background: "#fff",
                                        boxShadow:
                                            "0 3px 0 rgba(180,50,110,.25)",
                                        display: "flex",
                                        alignItems: "center",
                                        justifyContent: "center",
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
                            onClick={onOpenQuiz}
                            style={{
                                gridColumn: "1 / -1",
                                position: "relative",
                                overflow: "hidden",
                                borderRadius: 24,
                                padding: "16px 18px",
                                background:
                                    "linear-gradient(120deg,#2a2f6b 0%,#4b3ea8 55%,#6d4bd6 100%)",
                                boxShadow: "0 6px 0 rgba(35,25,90,.3)",
                                cursor: "pointer",
                                display: "flex",
                                alignItems: "center",
                                gap: 14,
                                border: 0,
                                width: "100%",
                                textAlign: "left",
                            }}
                        >
                            <span
                                style={{
                                    position: "absolute",
                                    right: -24,
                                    top: -30,
                                    width: 120,
                                    height: 120,
                                    borderRadius: "50%",
                                    background: "rgba(255,255,255,.08)",
                                }}
                            />
                            <span
                                style={{
                                    position: "relative",
                                    width: 52,
                                    height: 52,
                                    flex: "none",
                                    borderRadius: 16,
                                    background: "#ffd23f",
                                    boxShadow: "0 4px 0 rgba(0,0,0,.22)",
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "center",
                                    font: "800 30px/1 'Baloo 2'",
                                    color: "#2a2f6b",
                                }}
                            >
                                ?
                            </span>
                            <span
                                style={{
                                    position: "relative",
                                    flex: 1,
                                    minWidth: 0,
                                }}
                            >
                                <span
                                    style={{
                                        display: "block",
                                        font: "800 21px/1 'Baloo 2'",
                                        color: "#fff",
                                    }}
                                >
                                    Quiz Time
                                </span>
                                <span
                                    style={{
                                        display: "block",
                                        font: "800 11.5px 'Nunito'",
                                        color: "#d6d2ff",
                                        marginTop: 4,
                                    }}
                                >
                                    Test what you learned · all categories
                                </span>
                            </span>
                            <span
                                style={{
                                    position: "relative",
                                    borderRadius: 999,
                                    background: "#ffd23f",
                                    boxShadow: "0 3px 0 rgba(0,0,0,.22)",
                                    padding: "9px 16px",
                                    font: "800 13px 'Baloo 2'",
                                    color: "#2a2f6b",
                                }}
                            >
                                Play
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
