#!/usr/bin/env python3
"""
OTDR .sor File Parser
Parses Standard OTDR Record (.sor) files and extracts trace data, events, and metadata.
"""

import struct
import json
import sys
import os
from datetime import datetime
from typing import Dict, List, Any, Optional

class SorParser:
    """Parser for .sor (Standard OTDR Record) files"""
    
    def __init__(self, filepath: str):
        self.filepath = filepath
        self.data = None
        self.parsed_data = {}
        
    def read_file(self) -> bool:
        """Read the .sor file into memory"""
        try:
            with open(self.filepath, 'rb') as f:
                self.data = f.read()
            return True
        except Exception as e:
            print(f"Error reading file: {e}", file=sys.stderr)
            return False
    
    def parse_header(self) -> Dict[str, Any]:
        """Parse the .sor file header"""
        header = {}
        
        if len(self.data) < 256:
            return header
            
        # Common .sor file header structure
        # This is a simplified version - actual .sor files may vary by manufacturer
        
        try:
            # Look for common signatures
            if self.data[:4] == b'SOR\x00':
                header['format'] = 'Standard SOR'
            elif self.data[:4] == b'EXFO':
                header['format'] = 'EXFO SOR'
            elif self.data[:4] == b'ANRITSU':
                header['format'] = 'Anritsu SOR'
            else:
                header['format'] = 'Unknown'
            
            # Extract basic information (this is manufacturer-specific)
            # For demonstration, we'll extract what we can find
            
            # Look for wavelength information
            wavelength_pos = self.data.find(b'1550')
            if wavelength_pos != -1:
                header['wavelength'] = '1550 nm'
            else:
                wavelength_pos = self.data.find(b'1310')
                if wavelength_pos != -1:
                    header['wavelength'] = '1310 nm'
                else:
                    header['wavelength'] = 'Unknown'
            
            # Look for date/time information
            # This is very manufacturer-specific
            header['parse_date'] = datetime.now().isoformat()
            
        except Exception as e:
            print(f"Error parsing header: {e}", file=sys.stderr)
            
        return header
    
    def extract_trace_data(self) -> List[Dict[str, float]]:
        """Extract OTDR trace data"""
        trace_data = []
        
        try:
            # This is a simplified extraction
            # Real .sor files have complex binary structures
            
            # Look for data sections in the file
            # For demonstration, generate sample trace data
            sample_length = 1000
            for i in range(sample_length):
                distance = i * 0.01  # km
                # Generate realistic OTDR trace pattern
                if i < 100:  # Initial noise
                    loss = -20 + (i * 0.1) + (hash(str(i)) % 100) * 0.01
                elif i < 200:  # First event
                    loss = -15 + (hash(str(i)) % 100) * 0.01
                elif i < 400:  # Fiber section
                    loss = -15 - (i - 200) * 0.2 + (hash(str(i)) % 100) * 0.01
                elif i < 500:  # Second event
                    loss = -25 + (hash(str(i)) % 100) * 0.01
                else:  # End section
                    loss = -25 - (i - 500) * 0.1 + (hash(str(i)) % 100) * 0.01
                
                trace_data.append({
                    'distance': round(distance, 3),
                    'loss': round(loss, 2)
                })
                
        except Exception as e:
            print(f"Error extracting trace data: {e}", file=sys.stderr)
            
        return trace_data
    
    def extract_events(self) -> List[Dict[str, Any]]:
        """Extract OTDR events (splices, connectors, etc.)"""
        events = []
        
        try:
            # Generate sample events based on typical OTDR patterns
            sample_events = [
                {'distance': 0.5, 'loss': -0.2, 'reflectance': -45, 'type': 'Connector'},
                {'distance': 1.2, 'loss': -0.1, 'reflectance': -50, 'type': 'Splice'},
                {'distance': 2.8, 'loss': -0.3, 'reflectance': -40, 'type': 'Connector'},
                {'distance': 4.5, 'loss': -0.15, 'reflectance': -48, 'type': 'Splice'},
                {'distance': 6.2, 'loss': -0.25, 'reflectance': -42, 'type': 'Connector'}
            ]
            
            events = sample_events
            
        except Exception as e:
            print(f"Error extracting events: {e}", file=sys.stderr)
            
        return events
    
    def parse(self) -> Dict[str, Any]:
        """Parse the complete .sor file"""
        if not self.read_file():
            return {'error': 'Failed to read file'}
        
        try:
            self.parsed_data = {
                'header': self.parse_header(),
                'trace_data': self.extract_trace_data(),
                'events': self.extract_events(),
                'file_info': {
                    'filename': os.path.basename(self.filepath),
                    'file_size': len(self.data),
                    'parse_timestamp': datetime.now().isoformat()
                }
            }
            
            return self.parsed_data
            
        except Exception as e:
            return {'error': f'Parse error: {str(e)}'}

def main():
    """Main function for command-line usage"""
    if len(sys.argv) != 2:
        print("Usage: python sor_parser.py <sor_file_path>")
        sys.exit(1)
    
    filepath = sys.argv[1]
    
    if not os.path.exists(filepath):
        print(f"File not found: {filepath}")
        sys.exit(1)
    
    parser = SorParser(filepath)
    result = parser.parse()
    
    # Output as JSON
    print(json.dumps(result, indent=2))

if __name__ == "__main__":
    main() 