export function WaveDefs() {
    return (
        <svg
            width={0}
            height={0}
            style={{ position: "absolute" }}
            aria-hidden="true"
        >
            <defs>
                <clipPath id="hdrWave" clipPathUnits="objectBoundingBox">
                    <path d="M0,0 L1,0 L1,0.82 C0.84,1 0.66,0.76 0.5,0.85 C0.33,0.94 0.16,1.02 0,0.86 Z" />
                </clipPath>
            </defs>
        </svg>
    );
}
