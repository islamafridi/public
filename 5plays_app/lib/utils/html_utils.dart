import 'package:html/parser.dart' as html_parser;
import 'package:html/dom.dart' as dom;

class HtmlUtils {
  /// Strip HTML tags and return plain text
  static String stripHtml(String htmlString) {
    if (htmlString.isEmpty) return '';

    final document = html_parser.parse(htmlString);
    final String parsedString = parse(document.body?.text ?? '');

    return parsedString.trim();
  }

  /// Parse and clean text content
  static String parse(String data) {
    return data
        .replaceAll(RegExp(r'\s+'), ' ') // Replace multiple spaces with single space
        .replaceAll(RegExp(r'\n+'), '\n') // Replace multiple newlines with single newline
        .trim();
  }

  /// Convert HTML to formatted text with basic formatting preserved
  static String htmlToFormattedText(String htmlString) {
    if (htmlString.isEmpty) return '';

    final document = html_parser.parse(htmlString);
    return _processNode(document.body);
  }

  static String _processNode(dom.Node? node) {
    if (node == null) return '';

    String result = '';

    for (var child in node.nodes) {
      if (child.nodeType == dom.Node.TEXT_NODE) {
        result += child.text ?? '';
      } else if (child.nodeType == dom.Node.ELEMENT_NODE) {
        final element = child as dom.Element;
        final tagName = element.localName?.toLowerCase();

        switch (tagName) {
          case 'p':
            result += '\n${_processNode(child)}\n';
            break;
          case 'br':
            result += '\n';
            break;
          case 'li':
            result += '• ${_processNode(child)}\n';
            break;
          case 'ul':
          case 'ol':
            result += '\n${_processNode(child)}\n';
            break;
          case 'h1':
          case 'h2':
          case 'h3':
          case 'h4':
          case 'h5':
          case 'h6':
            result += '\n${_processNode(child)}\n';
            break;
          case 'strong':
          case 'b':
            result += _processNode(child);
            break;
          case 'em':
          case 'i':
            result += _processNode(child);
            break;
          default:
            result += _processNode(child);
        }
      }
    }

    return result;
  }
}