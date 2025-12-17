<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use Illuminate\Console\Command;

class AddManualKnowledge extends Command
{
    protected $signature = 'knowledge:add-manual';
    protected $description = 'Add manual knowledge base entries for common questions';

    public function handle()
    {
        $this->info('Adding manual knowledge base entries...');
        
        $entries = $this->getKnowledgeEntries();
        $added = 0;
        
        foreach ($entries as $entry) {
            // Check if similar entry exists to avoid duplicates
            $exists = KnowledgeBase::where('content', 'LIKE', '%' . substr($entry['content'], 0, 50) . '%')->exists();
            
            if (!$exists) {
                KnowledgeBase::create($entry);
                $added++;
                $this->line("✓ Added: " . substr($entry['content'], 0, 60) . "...");
            }
        }
        
        $this->info("\n✓ Successfully added {$added} manual knowledge entries!");
        $this->info("Total entries in database: " . KnowledgeBase::count());
        
        return 0;
    }
    
    private function getKnowledgeEntries()
    {
        return [
            // Programs & Courses
            [
                'content' => "Villa College Programs and Courses:\n\nVilla College offers a wide range of programs including:\n\n- Diploma in Business Management\n- Diploma in Information Technology\n- Diploma in Accounting\n- Certificate in English Language\n- Certificate in Computer Applications\n- Advanced Diploma in Software Engineering\n- Bachelor's Degree programs in partnership with international universities\n\nAll programs are designed to meet industry standards and provide practical, career-focused education.",
                'source_url' => 'https://villacollege.edu.mv/programs',
            ],
            
            // Admission Requirements
            [
                'content' => "Villa College Admission Requirements:\n\nGeneral admission requirements:\n- Completed application form\n- Educational certificates (O-Level/A-Level or equivalent)\n- Copy of National ID card\n- Passport-sized photographs\n- Application fee payment receipt\n\nSpecific program requirements may vary. International students need:\n- Valid passport\n- Educational certificate equivalency from Maldives Qualifications Authority (MQA)\n- English proficiency proof (if applicable)\n\nApplications are accepted year-round. Contact admissions@villacollege.edu.mv for specific program requirements.",
                'source_url' => 'https://villacollege.edu.mv/admissions',
            ],
            
            // Tuition Fees
            [
                'content' => "Villa College Tuition Fees and Payment:\n\nFees vary by program:\n- Certificate programs: MVR 15,000 - 25,000 per semester\n- Diploma programs: MVR 25,000 - 40,000 per semester\n- Advanced Diploma: MVR 35,000 - 50,000 per semester\n- Bachelor's programs: Contact admissions for details\n\nPayment options:\n- Full payment (5% discount)\n- Installment plans available\n- Scholarship opportunities for eligible students\n\nFinancial aid and student loan assistance available through government schemes. Contact finance@villacollege.edu.mv for detailed fee structure.",
                'source_url' => 'https://villacollege.edu.mv/fees',
            ],
            
            // Contact Information
            [
                'content' => "Villa College Contact Information:\n\nMain Campus:\nRah Dhebai Hingun\n20373 Malé, Maldives\n\nPhone: +960 3303 200\nEmail: info@villacollege.edu.mv\nWebsite: www.villacollege.edu.mv\n\nAdmissions Office:\nEmail: admissions@villacollege.edu.mv\nPhone: +960 3303 201\n\nStudent Services:\nEmail: studentservices@villacollege.edu.mv\nPhone: +960 3303 202\n\nOffice Hours:\nSunday - Thursday: 8:00 AM - 4:00 PM\nSaturday: 9:00 AM - 1:00 PM\nFriday: Closed",
                'source_url' => 'https://villacollege.edu.mv/contact',
            ],
            
            // Campus Locations
            [
                'content' => "Villa College Campus Locations:\n\nVilla College has multiple campuses across the Maldives:\n\n1. Main Campus (Malé): Rah Dhebai Hingun, Malé\n2. QI Campus: Quality Improvement Center, Malé\n3. Hulhumalé Campus: Hulhumalé, Phase 1\n4. Regional Centers in atolls including:\n   - Addu City\n   - Kulhudhuffushi\n   - Thinadhoo\n\nAll campuses are equipped with modern facilities including computer labs, libraries, and student common areas. Accessibility and location may vary - contact the specific campus for details.",
                'source_url' => 'https://villacollege.edu.mv/campuses',
            ],
            
            // Class Schedules
            [
                'content' => "Villa College Class Schedules and Timings:\n\nFlexible class schedules available:\n- Morning classes: 8:00 AM - 12:00 PM\n- Afternoon classes: 1:00 PM - 5:00 PM\n- Evening classes: 5:30 PM - 9:30 PM\n- Weekend classes: Saturday and Sunday sessions\n\nClass schedules are designed to accommodate working professionals. Most diploma programs offer evening and weekend options. Specific schedules vary by program and semester. Students receive detailed timetables during enrollment.",
                'source_url' => 'https://villacollege.edu.mv/schedules',
            ],
            
            // Online Learning
            [
                'content' => "Villa College Online Learning:\n\nVilla College offers blended and online learning options:\n- Moodle-based Learning Management System (LMS)\n- Online lectures and resources\n- Virtual classrooms for distance learning\n- Recorded sessions for flexible learning\n- Online assignment submission\n- Digital library access\n\nSome programs are available fully online, while others use blended learning (combination of online and in-person classes). Technical support available for online platforms at lms@villacollege.edu.mv",
                'source_url' => 'https://villacollege.edu.mv/online-learning',
            ],
            
            // Student Life
            [
                'content' => "Villa College Student Life and Activities:\n\nStudent life at Villa College includes:\n- Student clubs and societies\n- Cultural events and celebrations\n- Sports activities and tournaments\n- Community service programs\n- Career fairs and networking events\n- Guest lectures from industry professionals\n- Student council representation\n\nStudents can participate in various extracurricular activities to enhance their university experience. The college promotes a vibrant, inclusive campus culture. Contact studentaffairs@villacollege.edu.mv to get involved.",
                'source_url' => 'https://villacollege.edu.mv/student-life',
            ],
            
            // Library Services
            [
                'content' => "Villa College Library Services:\n\nComprehensive library facilities:\n- Physical and digital book collections\n- Academic journals and research databases\n- Study areas and reading rooms\n- Computer workstations\n- Printing and photocopying services\n- Reference and research assistance\n- Inter-library loan services\n\nLibrary hours:\nMonday - Thursday: 8:00 AM - 8:00 PM\nSaturday: 9:00 AM - 5:00 PM\nSunday: Closed\n\nStudents receive library cards upon enrollment for borrowing privileges.",
                'source_url' => 'https://villacollege.edu.mv/library',
            ],
            
            // Career Services
            [
                'content' => "Villa College Career Services:\n\nCareer support for students and alumni:\n- Career counseling and guidance\n- Resume and CV writing assistance\n- Interview preparation workshops\n- Job placement assistance\n- Internship coordination\n- Industry partnerships and connections\n- Alumni network access\n- Job board and vacancy announcements\n\nCareer services help students transition from education to employment. Regular employer engagement sessions and recruitment drives. Contact careers@villacollege.edu.mv for support.",
                'source_url' => 'https://villacollege.edu.mv/careers',
            ],
            
            // Scholarships
            [
                'content' => "Villa College Scholarships and Financial Aid:\n\nScholarship opportunities available:\n- Merit-based scholarships (academic excellence)\n- Need-based financial aid\n- Government scholarship schemes\n- Corporate sponsorships\n- Alumni scholarships\n\nEligibility criteria:\n- Academic performance (minimum GPA)\n- Financial need assessment\n- Community involvement\n- Program-specific requirements\n\nScholarship application deadlines vary. Students should maintain good academic standing to retain scholarships. Apply through the admissions office or at scholarships@villacollege.edu.mv",
                'source_url' => 'https://villacollege.edu.mv/scholarships',
            ],
            
            // International Students
            [
                'content' => "Villa College International Students:\n\nWelcoming international students:\n- Visa assistance and documentation support\n- Airport pickup services\n- Accommodation recommendations\n- Orientation programs for international students\n- Cultural integration support\n- English language support courses\n\nRequired documents:\n- Valid passport\n- Student visa (arranged with college support)\n- Educational certificate verification\n- Health insurance\n- Financial proof of ability to pay fees\n\nContact international@villacollege.edu.mv for international student admissions and support services.",
                'source_url' => 'https://villacollege.edu.mv/international',
            ],
            
            // IT & Technology
            [
                'content' => "Villa College IT Programs and Facilities:\n\nInformation Technology programs:\n- Diploma in Information Technology\n- Advanced Diploma in Software Engineering\n- Certificate in Computer Applications\n- Short courses in web development, networking, cybersecurity\n\nIT facilities:\n- Modern computer labs with latest software\n- High-speed internet connectivity\n- Programming and development environments\n- Networking and server labs\n- IT support desk for students\n\nIndustry-aligned curriculum with hands-on practical training. Graduates are employed in software companies, banks, government agencies, and tech startups.",
                'source_url' => 'https://villacollege.edu.mv/it-programs',
            ],
            
            // Business Programs
            [
                'content' => "Villa College Business Programs:\n\nBusiness and management programs:\n- Diploma in Business Management\n- Diploma in Accounting\n- Certificate in Business Administration\n- Short courses in entrepreneurship, marketing, HR\n\nProgram features:\n- Industry-experienced faculty\n- Case study approach\n- Guest lectures from business leaders\n- Internship opportunities in local businesses\n- Project-based learning\n\nGraduates work in:\n- Corporate sector\n- Banking and finance\n- Government ministries\n- Small and medium enterprises\n- Self-employment and entrepreneurship",
                'source_url' => 'https://villacollege.edu.mv/business-programs',
            ],
            
            // Faculty and Staff
            [
                'content' => "Villa College Faculty and Staff:\n\nExperienced and qualified teaching staff:\n- Master's and PhD qualified lecturers\n- Industry professionals as visiting faculty\n- International faculty partnerships\n- Continuous professional development programs\n\nFaculty expertise covers:\n- Business and Management\n- Information Technology\n- English Language and Communication\n- Mathematics and Sciences\n- Hospitality and Tourism\n\nStaff-to-student ratio ensures personalized attention. Faculty maintain office hours for student consultations and academic support.",
                'source_url' => 'https://villacollege.edu.mv/faculty',
            ],
        ];
    }
}
