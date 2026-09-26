import { Head } from "@inertiajs/react";
import { LearningApp } from "@/features/learning/LearningApp";

export default function Welcome() {
    return (
        <>
            <Head title="Learn & Grow">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link
                    rel="preconnect"
                    href="https://fonts.gstatic.com"
                    crossOrigin=""
                />
                <link
                    href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Baloo+Da+2:wght@600;700;800&family=Noto+Naskh+Arabic:wght@700&family=Nunito:wght@600;700;800&display=swap"
                    rel="stylesheet"
                />
                <style>{`
                    #app { min-height: 100%; }
                    *::-webkit-scrollbar { width: 0; height: 0; display: none; }
                    * { scrollbar-width: none; -ms-overflow-style: none; }
                    html, body { overscroll-behavior: none; touch-action: manipulation; }
                    * { -webkit-tap-highlight-color: transparent; -webkit-touch-callout: none; }
                    button { -webkit-user-select: none; user-select: none; }
                `}</style>
            </Head>

            <LearningApp />
        </>
    );
}
