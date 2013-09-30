<?
//---Determine width of string in Arial Font
//The width of each character was determined by typing the character into
//Microsoft word with Arial size 400 font and noting down the width as seen on the top margin

function ArialWidth($pString){
    $ArialWidth = 0;
    for ($i=0;$i<strlen($pString);$i++) {
        $character = $pString{$i};
        switch ($character) {
//upper case
            case 'A': $ArialWidth = $ArialWidth + 95; break;
            case 'B': $ArialWidth = $ArialWidth + 95; break;
            case 'C': $ArialWidth = $ArialWidth + 100; break;
            case 'D': $ArialWidth = $ArialWidth + 100; break;
            case 'E': $ArialWidth = $ArialWidth + 95; break;
            case 'F': $ArialWidth = $ArialWidth + 85; break;
            case 'G': $ArialWidth = $ArialWidth + 110; break;
            case 'H': $ArialWidth = $ArialWidth + 100; break;
            case 'I': $ArialWidth = $ArialWidth + 40; break;
            case 'J': $ArialWidth = $ArialWidth + 70; break;
            case 'K': $ArialWidth = $ArialWidth + 95; break;
            case 'L': $ArialWidth = $ArialWidth + 80; break;
            case 'M': $ArialWidth = $ArialWidth + 115; break;
            case 'N': $ArialWidth = $ArialWidth + 100; break;
            case 'O': $ArialWidth = $ArialWidth + 110; break;
            case 'P': $ArialWidth = $ArialWidth + 95; break;
            case 'Q': $ArialWidth = $ArialWidth + 110; break;
            case 'R': $ArialWidth = $ArialWidth + 100; break;
            case 'S': $ArialWidth = $ArialWidth + 95; break;
            case 'T': $ArialWidth = $ArialWidth + 85; break;
            case 'U': $ArialWidth = $ArialWidth + 100; break;
            case 'V': $ArialWidth = $ArialWidth + 95; break;
            case 'W': $ArialWidth = $ArialWidth + 130; break;
            case 'X': $ArialWidth = $ArialWidth + 95; break;
            case 'Y': $ArialWidth = $ArialWidth + 95; break;
            case 'Z': $ArialWidth = $ArialWidth + 85; break;
//lowercase
            case 'a': $ArialWidth = $ArialWidth + 80; break;
            case 'b': $ArialWidth = $ArialWidth + 80; break;
            case 'c': $ArialWidth = $ArialWidth + 70; break;
            case 'd': $ArialWidth = $ArialWidth + 80; break;
            case 'e': $ArialWidth = $ArialWidth + 80; break;
            case 'f': $ArialWidth = $ArialWidth + 40; break;
            case 'g': $ArialWidth = $ArialWidth + 80; break;
            case 'h': $ArialWidth = $ArialWidth + 80; break;
            case 'i': $ArialWidth = $ArialWidth + 30; break;
            case 'j': $ArialWidth = $ArialWidth + 30; break;
            case 'k': $ArialWidth = $ArialWidth + 70; break;
            case 'l': $ArialWidth = $ArialWidth + 30; break;
            case 'm': $ArialWidth = $ArialWidth + 120; break;
            case 'n': $ArialWidth = $ArialWidth + 80; break;
            case 'o': $ArialWidth = $ArialWidth + 80; break;
            case 'p': $ArialWidth = $ArialWidth + 80; break;
            case 'q': $ArialWidth = $ArialWidth + 80; break;
            case 'r': $ArialWidth = $ArialWidth + 45; break;
            case 's': $ArialWidth = $ArialWidth + 70; break;
            case 't': $ArialWidth = $ArialWidth + 40; break;
            case 'u': $ArialWidth = $ArialWidth + 80; break;
            case 'v': $ArialWidth = $ArialWidth + 70; break;
            case 'w': $ArialWidth = $ArialWidth + 100; break;
            case 'x': $ArialWidth = $ArialWidth + 70; break;
            case 'y': $ArialWidth = $ArialWidth + 70; break;
            case 'z': $ArialWidth = $ArialWidth + 70; break;
//numbers
            case '0': $ArialWidth = $ArialWidth + 80; break;
            case '1': $ArialWidth = $ArialWidth + 80; break;
            case '2': $ArialWidth = $ArialWidth + 80; break;
            case '3': $ArialWidth = $ArialWidth + 80; break;
            case '4': $ArialWidth = $ArialWidth + 80; break;
            case '5': $ArialWidth = $ArialWidth + 80; break;
            case '6': $ArialWidth = $ArialWidth + 80; break;
            case '7': $ArialWidth = $ArialWidth + 80; break;
            case '8': $ArialWidth = $ArialWidth + 80; break;
            case '9': $ArialWidth = $ArialWidth + 80; break;
//symbols
            case ' ': $ArialWidth = $ArialWidth + 45; break;
            case '.': $ArialWidth = $ArialWidth + 40; break;
            case ',': $ArialWidth = $ArialWidth + 40; break;
            case "'": $ArialWidth = $ArialWidth + 30; break;
            case '-': $ArialWidth = $ArialWidth + 45; break;
            case '/': $ArialWidth = $ArialWidth + 40; break;
            case '"': $ArialWidth = $ArialWidth + 45; break;
            case '_': $ArialWidth = $ArialWidth + 80; break;
            case ')': $ArialWidth = $ArialWidth + 45; break;
            case '(': $ArialWidth = $ArialWidth + 45; break;
//and the rest
            default: $ArialWidth = $ArialWidth + 90; break;
        }
    }
    return $ArialWidth;
}
?>
