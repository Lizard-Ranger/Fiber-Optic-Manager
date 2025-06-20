# OTDR .sor Files and Viewer Implementation

## What are .sor files?

**.sor files** (Standard OTDR Record) are binary files that contain OTDR (Optical Time Domain Reflectometer) measurement data. They are the industry standard format for storing OTDR test results and are used extensively in fiber-optic network testing and maintenance.

### Key Components of .sor Files

1. **Header Information**
   - File format version
   - Test parameters (wavelength, pulse width, range)
   - Date and time of measurement
   - Operator information
   - Fiber specifications

2. **Trace Data**
   - Raw OTDR measurement curve
   - Distance vs. loss data points
   - Signal strength measurements
   - Noise floor information

3. **Event Data**
   - Splice locations and losses
   - Connector information
   - Reflection events
   - Fiber breaks or faults

4. **Metadata**
   - Test equipment information
   - Calibration data
   - Environmental conditions
   - Test notes and comments

## Software Solutions for .sor Files

### Commercial Software
- **EXFO LinkWare**: Industry standard for OTDR analysis
- **Viavi T-BERD**: Professional OTDR analysis software
- **Anritsu MT9080**: Advanced OTDR testing and analysis
- **JDSU MTS/T-BERD**: Comprehensive OTDR solutions

### Open Source Solutions
- **Python Libraries**:
  - `pysor`: Python library for reading .sor files
  - `otdr-parser`: Open source OTDR file parser
  - `fibertools`: Comprehensive fiber testing toolkit

- **JavaScript Libraries**:
  - `sor-parser-js`: JavaScript library for parsing .sor files
  - `otdr-viewer`: Web-based OTDR trace viewer

## Implementation in Fiber-Optic Manager

### Files Created

1. **`otdr_viewer.php`**
   - Main OTDR viewer interface
   - File upload functionality
   - Interactive chart display
   - Event table display
   - Integration with database

2. **`sor_parser.py`**
   - Python script for parsing .sor files
   - Extracts trace data, events, and metadata
   - Handles different .sor file formats
   - Returns structured JSON data

3. **`sor_parser_api.php`**
   - PHP API endpoint
   - Bridges PHP frontend with Python parser
   - Handles file uploads and security
   - Returns JSON responses

### Features

#### File Upload and Management
- Secure file upload with validation
- Automatic file storage in `uploads/otdr/` directory
- Unique filename generation to prevent conflicts
- Database integration for file tracking

#### OTDR Data Display
- **Interactive Charts**: Using Chart.js for trace visualization
- **Event Tables**: Detailed event information display
- **Test Information**: Header data and metadata display
- **Responsive Design**: Works on desktop and mobile devices

#### Integration with Existing System
- Links from network map, reports, and admin pages
- Connection-specific OTDR data storage
- Seamless integration with existing database schema

### Usage Instructions

#### Uploading .sor Files
1. Navigate to any connection in the system
2. Click "Upload OTDR" or "View OTDR" button
3. Select a .sor file from your computer
4. Click "Upload and Analyze"
5. View the parsed OTDR data

#### Viewing Existing OTDR Data
1. Go to a connection with existing OTDR data
2. Click "View OTDR" button
3. Use "Load from Database" to view stored data
4. Or use "Load Existing .sor File" to analyze new files

#### Manual File Analysis
1. Use the "Load Existing .sor File" section
2. Select any .sor file from your computer
3. Click "Load File" to analyze without uploading

### Technical Implementation

#### Python Parser (`sor_parser.py`)
```python
class SorParser:
    def __init__(self, filepath: str):
        self.filepath = filepath
        self.data = None
        self.parsed_data = {}
    
    def parse_header(self) -> Dict[str, Any]:
        # Parse file header information
        # Extract format, wavelength, date, etc.
    
    def extract_trace_data(self) -> List[Dict[str, float]]:
        # Extract OTDR trace data points
        # Convert binary data to distance/loss pairs
    
    def extract_events(self) -> List[Dict[str, Any]]:
        # Extract event information
        # Splices, connectors, reflections, etc.
```

#### PHP API Integration
```php
// Handle file upload
if (isset($_FILES['sor_file'])) {
    // Validate and save file
    // Call Python parser
    // Return JSON response
}

// Handle existing file parsing
if (isset($_GET['filepath'])) {
    // Security validation
    // Call Python parser
    // Return JSON response
}
```

#### JavaScript Chart Display
```javascript
function displayOtdrData(data) {
    // Create Chart.js visualization
    // Display trace data as line chart
    // Show events table
    // Display test information
}
```

### Security Considerations

1. **File Upload Security**
   - File type validation (.sor only)
   - File size limits
   - Secure file storage location
   - Unique filename generation

2. **Path Traversal Protection**
   - Real path validation
   - Upload directory restriction
   - File existence checks

3. **Command Injection Prevention**
   - Escaped shell arguments
   - Input validation
   - Error handling

### File Structure

```
/var/www/html/
├── otdr_viewer.php          # Main OTDR viewer interface
├── sor_parser.py            # Python .sor file parser
├── sor_parser_api.php       # PHP API endpoint
├── uploads/
│   └── otdr/               # OTDR file storage
└── docs/
    └── OTDR_SOR_FILES.md   # This documentation
```

### Dependencies

#### Server Requirements
- PHP 7.4+ with file upload support
- Python 3.6+ with standard libraries
- Web server with PHP execution capability
- Sufficient disk space for file uploads

#### JavaScript Libraries
- Chart.js (CDN): For interactive charts
- Fetch API: For AJAX requests

#### Python Libraries
- Standard library only (struct, json, sys, os, datetime, typing)

### Troubleshooting

#### Common Issues

1. **File Upload Fails**
   - Check file permissions on upload directory
   - Verify PHP upload settings in php.ini
   - Ensure sufficient disk space

2. **Python Parser Errors**
   - Verify Python 3 is installed and accessible
   - Check file permissions on sor_parser.py
   - Ensure .sor file is valid and not corrupted

3. **Chart Display Issues**
   - Check browser console for JavaScript errors
   - Verify Chart.js CDN is accessible
   - Ensure parsed data is in correct format

#### Debug Mode
Enable debug mode by adding to PHP files:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Future Enhancements

1. **Advanced .sor Parsing**
   - Support for more manufacturer formats
   - Better event detection algorithms
   - Enhanced metadata extraction

2. **Additional Features**
   - PDF report generation
   - Data export to Excel/CSV
   - Batch file processing
   - Historical data comparison

3. **Integration Improvements**
   - Real-time OTDR data updates
   - Automated fault detection
   - Integration with network monitoring systems

### References

- [ITU-T G.650.1](https://www.itu.int/rec/T-REC-G.650.1): Definitions and test methods for linear, deterministic attributes of single-mode fibre and cable
- [IEC 61746-1](https://webstore.iec.ch/publication/21975): Calibration of OTDR
- [EXFO SOR File Format Documentation](https://www.exfo.com/en/)
- [Anritsu OTDR Documentation](https://www.anritsu.com/en-us/test-measurement)

### Support

For technical support or questions about .sor file implementation:
- Check the troubleshooting section above
- Review server error logs
- Verify file permissions and dependencies
- Test with known good .sor files

**Note:** The splice map now uses composite core identifiers (e.g., A-Orange-2) and the fiber_tubes table for advanced mapping and color-coding. 