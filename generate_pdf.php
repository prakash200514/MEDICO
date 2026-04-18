<?php
// This script will help you convert the HTML documentation to PDF
// You can use this with tools like wkhtmltopdf or browser print functionality

echo "<h2>PDF Generation Instructions</h2>";

echo "<h3>Method 1: Browser Print to PDF (Recommended)</h3>";
echo "<ol>";
echo "<li>Open the ER diagram documentation: <a href='medico_er_diagram.html' target='_blank'>Open Documentation</a></li>";
echo "<li>Press Ctrl+P (or Cmd+P on Mac) to open print dialog</li>";
echo "<li>Select 'Save as PDF' as destination</li>";
echo "<li>Choose 'A4' paper size</li>";
echo "<li>Set margins to 'Minimum'</li>";
echo "<li>Enable 'Background graphics' option</li>";
echo "<li>Click 'Save' to generate PDF</li>";
echo "</ol>";

echo "<h3>Method 2: Using wkhtmltopdf (Command Line)</h3>";
echo "<p>If you have wkhtmltopdf installed:</p>";
echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px; font-family: monospace;'>";
echo "wkhtmltopdf --page-size A4 --margin-top 10mm --margin-bottom 10mm --margin-left 10mm --margin-right 10mm --enable-local-file-access medico_er_diagram.html medico_er_diagram.pdf";
echo "</div>";

echo "<h3>Method 3: Online HTML to PDF Converters</h3>";
echo "<ul>";
echo "<li><a href='https://www.ilovepdf.com/html-to-pdf' target='_blank'>ILovePDF HTML to PDF</a></li>";
echo "<li><a href='https://smallpdf.com/html-to-pdf' target='_blank'>SmallPDF HTML to PDF</a></li>";
echo "<li><a href='https://www.sejda.com/html-to-pdf' target='_blank'>Sejda HTML to PDF</a></li>";
echo "</ul>";

echo "<h3>Documentation Features</h3>";
echo "<ul>";
echo "<li>✅ Complete ER Diagram with all tables and relationships</li>";
echo "<li>✅ Detailed database schema with SQL code</li>";
echo "<li>✅ System architecture and technology stack</li>";
echo "<li>✅ User workflow diagrams</li>";
echo "<li>✅ Security features documentation</li>";
echo "<li>✅ Admin panel features</li>";
echo "<li>✅ Technical specifications</li>";
echo "<li>✅ Future enhancement plans</li>";
echo "<li>✅ Print-optimized layout</li>";
echo "<li>✅ Professional styling and formatting</li>";
echo "</ul>";

echo "<h3>Quick Access</h3>";
echo "<p><a href='medico_er_diagram.html' target='_blank' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>📄 View ER Diagram Documentation</a></p>";
?>

