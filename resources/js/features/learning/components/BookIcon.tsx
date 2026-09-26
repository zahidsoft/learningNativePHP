import { S } from "@/features/learning/lib/styles";

export function BookIcon() {
    return (
        <span style={S.pillIcon}>
            <span
                style={{
                    display: "block",
                    width: 6,
                    height: 13,
                    borderRadius: "2px 0 0 2px",
                    background: "#fff",
                }}
            />
            <span
                style={{
                    display: "block",
                    width: 6,
                    height: 13,
                    borderRadius: "0 2px 2px 0",
                    background: "#fff",
                }}
            />
        </span>
    );
}
