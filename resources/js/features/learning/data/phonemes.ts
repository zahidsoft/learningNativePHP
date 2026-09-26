export type Phoneme = { symbol: string; isolation: string; words: string };

const PHONEME_DIR = "/audio/phoneme_audio";

const phoneme = (symbol: string): Phoneme => ({
    symbol,
    isolation: `${PHONEME_DIR}/${symbol}_isolation.mp3`,
    words: `${PHONEME_DIR}/${symbol}_words.mp3`,
});

/**
 * The everyday phonics sound(s) each English letter makes, mapped to its
 * matching clip pair in public/audio/phoneme_audio. C and G get the two
 * sounds every basic phonics curriculum teaches them with ("hard"/"soft");
 * every other letter lists just its one common sound.
 */
export const LETTER_PHONEMES: Record<string, Phoneme[]> = {
    A: [phoneme("æ")],
    B: [phoneme("b")],
    C: [phoneme("k"), phoneme("s")],
    D: [phoneme("d")],
    E: [phoneme("e")],
    F: [phoneme("f")],
    G: [phoneme("g"), phoneme("dʒ")],
    H: [phoneme("h")],
    I: [phoneme("ɪ")],
    J: [phoneme("dʒ")],
    K: [phoneme("k")],
    L: [phoneme("l")],
    M: [phoneme("m")],
    N: [phoneme("n")],
    O: [phoneme("ɒ")],
    P: [phoneme("p")],
    Q: [phoneme("k")],
    R: [phoneme("r")],
    S: [phoneme("s")],
    T: [phoneme("t")],
    U: [phoneme("ʌ")],
    V: [phoneme("v")],
    W: [phoneme("w")],
    X: [phoneme("x"), phoneme("z")],
    Y: [phoneme("j")],
    Z: [phoneme("z")],
};
