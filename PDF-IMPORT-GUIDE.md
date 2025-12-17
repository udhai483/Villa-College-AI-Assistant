# PDF Import Guide

## Overview
The PDF import feature allows you to extract text from PDF documents and add them to the Villa College AI Assistant knowledge base.

## Installation
Already installed! The `smalot/pdfparser` library is included.

## Usage

### Import a Single PDF
```bash
php artisan knowledge:import-pdf /path/to/document.pdf
```

### Import All PDFs from a Directory
```bash
php artisan knowledge:import-pdf storage/app/pdfs/
```

### Using Docker
```bash
docker compose exec app php artisan knowledge:import-pdf storage/app/pdfs/
```

## Recommended PDF Documents

For Villa College, import these types of documents:

### Academic Documents
- **Course Catalogs** - Program descriptions, curriculum details
- **Student Handbooks** - Policies, procedures, academic calendar
- **Prospectuses** - Admission information, program offerings
- **Course Syllabi** - Individual course details

### Administrative Documents
- **Policy Documents** - Academic policies, code of conduct
- **Fee Schedules** - Tuition fees, payment plans
- **Application Forms** - Admission requirements, deadlines

### Informational Brochures
- **Campus Guides** - Facilities, locations, maps
- **Program Brochures** - Specific program information
- **International Student Guides** - Visa, accommodation info

## How It Works

1. **PDF Parsing**: Uses Smalot PDF Parser to extract text
2. **Text Cleaning**: 
   - Removes page numbers and headers/footers
   - Fixes hyphenated words across line breaks
   - Normalizes whitespace
3. **Chunking**: Splits text into 500-800 character chunks at sentence boundaries
4. **Storage**: Saves chunks with `source_url` = `pdf://filename.pdf`
5. **Duplicate Detection**: Skips already imported PDFs

## Text Extraction Features

✅ **Encoding Normalization** - Handles UTF-8, special characters  
✅ **Metadata Extraction** - Uses PDF title if available  
✅ **Smart Chunking** - Respects sentence boundaries (500-800 chars)  
✅ **Noise Removal** - Removes page numbers, repeated headers/footers  
✅ **Hyphen Fixing** - Joins words split across lines  

## Example Output

```
Starting PDF import...

Found 3 PDF file(s) to process

✓ villa-college-prospectus-2025.pdf: 45 chunks
✓ student-handbook.pdf: 32 chunks
✓ course-catalog.pdf: 67 chunks

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PDF Import Summary:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
| Metric              | Value |
+---------------------+-------+
| PDFs Attempted      | 3     |
| Successful          | 3     |
| Failed              | 0     |
| Total Chunks Stored | 144   |
| Average per PDF     | 48    |
```

## Storage Location

Place PDFs in: `storage/app/pdfs/`

This directory is:
- ✅ Git-ignored (won't be committed)
- ✅ Writable by the application
- ✅ Inside Docker container volume

## Verification

Check imported PDFs in knowledge base:

```bash
docker compose exec app php artisan tinker
```

```php
// Count PDF entries
KnowledgeBase::where('source_url', 'LIKE', 'pdf://%')->count();

// List PDF sources
KnowledgeBase::where('source_url', 'LIKE', 'pdf://%')
    ->distinct('source_url')
    ->pluck('source_url');

// Sample PDF content
KnowledgeBase::where('source_url', 'LIKE', 'pdf://%')
    ->first()
    ->content;
```

## Troubleshooting

### "No text content" Error
- PDF may be image-based (scanned) - needs OCR
- Try converting to text-based PDF first

### "Path not found" Error
- Check file path is correct
- Use absolute path or path relative to Laravel root

### Duplicate Detection
- Command skips PDFs already imported
- Based on filename in source_url
- To re-import, delete old entries first

## Best Practices

1. **Organize by Category**: Create subdirectories
   ```
   storage/app/pdfs/
   ├── academic/
   ├── admissions/
   ├── policies/
   └── programs/
   ```

2. **Use Descriptive Names**: `villa-college-prospectus-2025.pdf` not `doc1.pdf`

3. **Verify Quality**: Check that PDFs are text-based, not scanned images

4. **Regular Updates**: Re-run import when documents are updated

## Integration with Search

PDF content is automatically included in:
- ✅ Keyword search
- ✅ Phrase matching
- ✅ Relevance scoring
- ✅ Source URL display (shows "pdf://filename.pdf")

The chatbot will cite PDF sources in responses!

## Next Steps

After importing PDFs:
1. Test chatbot with questions from PDF content
2. Verify source URLs appear in responses
3. Add more PDFs as needed
4. (Optional) Generate embeddings for semantic search
