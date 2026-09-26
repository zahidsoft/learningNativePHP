import { ALL } from "@/features/learning/data/categories";
import type { Question } from "@/features/learning/types";

const shuffle = <T,>(a: T[]): T[] =>
    a
        .map((v) => [Math.random(), v] as const)
        .sort((x, y) => x[0] - y[0])
        .map((v) => v[1]);

export const makeQuestion = (): Question => {
    const pool = shuffle(ALL);
    const answer = pool[0];
    return {
        answer,
        opts: shuffle([
            answer,
            ...pool.filter((l) => l.name !== answer.name).slice(0, 3),
        ]),
    };
};
