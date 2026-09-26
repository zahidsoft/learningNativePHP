import type { CSSProperties } from "react";

export const STICKER =
    "3px 3px 0 #fff,-3px 3px 0 #fff,3px -3px 0 #fff,-3px -3px 0 #fff,0 7px 6px rgba(0,0,0,.16)";

export const S: Record<string, CSSProperties> = {
    shell: {
        width: "100%",
        maxWidth: 430,
        margin: "0 auto",
        minHeight: "100dvh",
        position: "relative",
        overflow: "hidden",
        background: "#eaf6ff",
        fontFamily: "'Nunito',system-ui,sans-serif",
    },
    screen: {
        position: "absolute",
        inset: 0,
        display: "flex",
        flexDirection: "column",
    },
    sky: {
        position: "absolute",
        inset: 0,
        display: "flex",
        flexDirection: "column",
        background:
            "linear-gradient(168deg,#f6f9ff 0%,#eef4fd 46%,#f7f3ff 100%)",
    },
    blooms: {
        position: "absolute",
        inset: 0,
        background:
            "radial-gradient(circle 220px at 108% 16%,rgba(94,162,255,.16),rgba(94,162,255,0) 70%)," +
            "radial-gradient(circle 200px at -12% 46%,rgba(255,168,86,.13),rgba(255,168,86,0) 70%)," +
            "radial-gradient(circle 240px at 90% 96%,rgba(138,96,240,.13),rgba(138,96,240,0) 70%)",
    },
    dots: {
        position: "absolute",
        inset: 0,
        opacity: 0.5,
        background:
            "radial-gradient(circle 1.4px at 1.4px 1.4px,rgba(38,74,130,.18) 1.4px,rgba(0,0,0,0) 1.5px) 0 0/22px 22px",
    },
    fade: {
        position: "absolute",
        left: 0,
        right: 0,
        bottom: 0,
        height: 150,
        background:
            "linear-gradient(180deg,rgba(247,249,255,0),rgba(255,255,255,.6))",
    },
    scroll: {
        flex: 1,
        minHeight: 0,
        overflowY: "auto",
        padding: "4px 16px 24px",
    } as CSSProperties,
    grid: { display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 },
    glyphRow: {
        position: "relative",
        height: 78,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        gap: 2,
    },
    pillIcon: {
        width: 28,
        height: 28,
        flex: "none",
        borderRadius: "50%",
        background: "rgba(255,255,255,.22)",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        gap: 2,
    },
    back: {
        width: 40,
        height: 40,
        flex: "none",
        borderRadius: "50%",
        background: "#fff",
        boxShadow: "0 3px 0 rgba(0,0,0,.14)",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        cursor: "pointer",
        border: 0,
    },
    chev: {
        display: "block",
        width: 11,
        height: 11,
        borderLeft: "4px solid #2c3e58",
        borderBottom: "4px solid #2c3e58",
        transform: "rotate(45deg)",
        marginLeft: -3,
    },
    ph: {
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        font: "700 7px ui-monospace,monospace",
        textAlign: "center",
        lineHeight: 1.1,
    },
};

export const card = (
    from: string,
    to: string,
    shadow: string,
    wide = false,
): CSSProperties => ({
    gridColumn: wide ? "1 / -1" : undefined,
    position: "relative",
    overflow: "hidden",
    borderRadius: 24,
    padding: "12px 10px 10px",
    background: `linear-gradient(180deg,${from},${to})`,
    boxShadow: `0 6px 0 ${shadow}`,
    cursor: "pointer",
    border: 0,
    width: "100%",
    textAlign: "left",
    font: "inherit",
});

export const pill = (bg: string, shadow: string): CSSProperties => ({
    position: "relative",
    marginTop: 8,
    borderRadius: 999,
    background: bg,
    boxShadow: `0 3px 0 ${shadow}`,
    padding: "7px 10px",
    display: "flex",
    alignItems: "center",
    gap: 8,
});

export const glyph = (
    color: string,
    size: number,
    family: string,
): CSSProperties => ({
    color,
    font: `${family === "'Baloo 2'" ? 800 : 700} ${size}px/1 ${family}`,
    textShadow: STICKER,
});
