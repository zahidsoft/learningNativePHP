import { useEffect } from "react";
import { vibrate } from "@/lib/native-bridge";
import type { NavState } from "@/features/learning/types";

/**
 * Wires screen navigation to browser history so the phone's hardware back
 * button (which the native shell maps to WebView.goBack() when history
 * exists, or exits the app otherwise) steps back through screens the same
 * way the on-screen back arrow does, instead of quitting the app from any
 * depth.
 */
export function useScreenHistory(
    applyState: (state: NavState) => void,
    initial: NavState,
) {
    useEffect(() => {
        if (typeof window === "undefined") return;

        if (
            !window.history.state ||
            window.history.state.screen === undefined
        ) {
            window.history.replaceState(initial, "");
        }

        const onPopState = (e: PopStateEvent) => {
            const s = e.state as NavState | null;
            if (!s) return;
            applyState(s);
        };

        window.addEventListener("popstate", onPopState);
        return () => window.removeEventListener("popstate", onPopState);
    }, []);

    const pushScreen = (next: NavState) => {
        applyState(next);
        window.history?.pushState(next, "");
        vibrate();
    };

    const goBack = () => {
        window.history.back();
    };

    return { pushScreen, goBack };
}
