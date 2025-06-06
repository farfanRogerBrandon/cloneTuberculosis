class CIVerifier {
  bool verifyCI(String ci) {
    bool res = false;
    if (ci.trim().isEmpty) return false;

    // Eliminar espacios en exceso
    ci = ci.trim().replaceAll(' ', '');

    // Validar en orden
    if (verifyForeignCI(ci)) {
      res = true;
    } else if (verifyDuplicateCI(ci)) {
      res = true;
    } else if (verifyCommonCI(ci)) {
      res = true;
    } else {
      res = false;
    }
    return res;
  }

  bool verifyForeignCI(String ci) {
    if (ci.length < 5) return false;
    if (!ci.startsWith("E-")) return false;
    String numeros = ci.substring(2);
    return numeros.length >= 5 && numeros.length <= 12 && _onlyNumbers(numeros);
  }

  bool verifyDuplicateCI(String ci) {
    if (!ci.contains("-") || ci.length > 15) return false;
    List<String> partes = ci.split('-');
    if (partes.length != 2) return false;

    String numeros = partes[0];
    String resto = partes[1];

    if (resto.length != 2) return false; // deben ser 2 caracteres exactos

    String penultimo = resto.substring(0, 1);
    String ultimo = resto.substring(1);
    return _onlyNumbers(numeros) &&
        !numeros.startsWith("0") &&
        numeros.length >= 5 &&
        numeros.length <= 12 &&
        _onlyNumbers(penultimo) &&
        _onlyLetter(ultimo);
  }

  bool verifyCommonCI(String ci) {
    ci = ci.trim().replaceAll(' ', ''); // eliminar espacios por si acaso
    return _onlyNumbers(ci) &&
        ci.length >= 5 &&
        ci.length <= 12 &&
        !ci.startsWith("0");
  }

  bool _onlyNumbers(String input) => RegExp(r'^\d+$').hasMatch(input);
  bool _onlyLetter(String input) => RegExp(r'^[a-zA-Z]$').hasMatch(input);
}
