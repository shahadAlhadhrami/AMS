 ### findings from [B.Tech_Phase_I_Proposal.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/B.Tech_Phase_I_Proposal.docx)

Course Projects assessments are being handled manually by different reviewers and supervisors, leading to delays, errors, and potential data breaches.

what if we create a system that make the whole process in one centralized place.

So now we are building a web based Assessment Management System, lets call it AMS.

this system needs to be secure, centralized web based system for managing and consolidating course project assessment marks from different reviewers and the supervisor that is entered in different stages (Proposal, First Review, Second Review, and Final Review), users will be able to generate reports.

==system will generate reports for students only ?==

obviously the system will have role-based access for different users that will be using the system.


==who will be using the system and what are there roles ?==
Reviewers , Supervisor, Coordinator, Students


this system will ensure secure data handling and enforce security principles including Confidentiality, Integrity, and Availability. it will also have Multi-Factor Authentication (MFA to make user auth more secure, also system will have Encryption at rest and in transit, we will be also having an Intrusion Detection and Activity Monitoring to identify and respond to unusual or unauthorized activities in real time.

==what ntrusion Detection and Activity Monitoring system will will be using ? we need to identify.==


these are the objectives from the PROPOSAL:
>1-    To create secure login interfaces for all assessors.
>2-    To enable entry of marks for Proposal, First Review, Second Review, and Final Review.
>3-    To consolidate all assessment marks automatically.
>4-    To enforce CIA (Confidentiality, Integrity, Availability) principles in system architecture and data handling.
>5-    To generate secure, auditable reports for academic records.

***i feel that we will be building something more than what have been stated above in the objectives***


Our tech stack will be:
Framework: Laravel
backend: PHP
frontend: HTML,CSS,javascript
databases : Mysql
development env: Laravel Herd (local web server)
tools: VS code, Github

idk if this is correct or not if yes, what are the specific tools to use
Security Tools: 
HTTPS, TLS, Role-Based Access Control, Audit Logging

| **Layer**                             | **Component**                                | **Why this is the "Best" choice**                                                                                                                                 |
| ------------------------------------- | -------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Backend Core**                      | **Laravel 11+**                              | The industry standard for PHP security. Handles your routing, encryption, and business logic with zero fluff.                                                     |
| **Admin & UI**                        | **FilamentPHP v5**                           | Provides the "shadcn" look with 10x the speed. It builds your secure tables, forms, and dashboard layouts automatically.                                          |
| **Database**                          | **MySQL 8.x**                                | Relational database to enforce **Integrity** via foreign keys (e.g., a grade cannot exist without a valid student ID).                                            |
| **Styling**                           | **Tailwind CSS**                             | Built directly into Filament; allows for rapid, professional UI customization without writing custom CSS files.                                                   |
| **Local Dev**                         | **Laravel Herd**                             | Herd is faster, secure, and uses Nginx, which mirrors real production servers.                                         |
| **MFA (Multi-Factor Authentication)** | **Laravel Fortify**                          | (integrated into Filament). This ensures that even if a Reviewer’s password is stolen, the data remains **Confidential**.                                         |
| Audit Logging (Integrity)             | Spatie Laravel Activitylog                   | Every time a mark is changed, the system will record _who_ did it, _when_, and what the _old vs. new_ values were. This is your "auditable report" for reviewers. |
| Intrusion Detection (IDS)             | Fail2Ban <br>and <br>Laravel Pwned Passwords | you will implement **Fail2Ban** (to block brute-force login attempts) and **Laravel Pwned Passwords** to prevent users from picking leaked passwords.             |
| Encryption                            | Encrypter                                    | Laravel’s native **Encrypter** (AES-256) for sensitive academic records at rest, and **Force HTTPS/TLS** for data in transit.                                     |


==are we suppose to implement security from scratch ?? ==

### Findings from [final BTech Project-Final Report Fall.docx](file:///Users/monta/Library/CloudStorage/OneDrive-UniversityofTechnologyandAppliedSciences/Clients/CP%20-%20AMS/final%20BTech%20Project-Final%20Report%20Fall.docx)


**inputs are:**
Project details, assessor credentials, and evaluation criteria entered by authorized users (Reviewer I, Reviewer II, and Supervisor)

**Process:**
The system authenticates users, validates data, applies role-based access control, stores marks in the secure database, and consolidates results automatically using predefined weights. Encryption, MFA, and audit logging ensure security during these operations.

**Output:**
Final consolidated assessment reports, dashboards for reviewers and supervisors, and securely stored audit logs for accountability.

Note:
- This website will be only hosted in the development environment, cloud hosting and deployment is out of scope. 
- The system focuses solely on Course Project assessments and does not include other academic components such as attendance or examination grading.

==can reviewers login to the system ? . in another words, will they be count as indented system user with unique role based access ?  ==

==will it be nice to have an a public evaluation form for non system users to evaluate the project like other students, this evaluation will not be a part of the marks reviews.==

==Students are going to upload their work to the system because its not in the objectives?? or will we just let them reference it from OneDrive ? ==

  
#### Assessment Rubrics
**Phase 1:**

# [Bachelor - Phase 1 - CP - Consolidated Mark Statment - 100 Marks 2.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%201/Bachelor%20-%20Phase%201%20-%20CP%20-%20Consolidated%20Mark%20Statment%20-%20100%20Marks%202.docx)
## **General Information Header**

- **Institution:** UTAS, Nizwa
    
- **Department:** Information Technology
    
- **Document Type:** Consolidated Mark Statement – Phase – I (To be filled by Supervisor)
    
- **Level:** Bachelor (Phase – 1)
    
- **Fields to be Filled:**
    
    - Semester and Academic Year
        
    - Project Title
        
    - Course Code and Course Title
        
    - Specialization
        
    - Date
        
    - Student ID and Name
        

---

## **Marking Breakdown (The Weights)**

The total assessment is out of **100 marks**, split between your internal Supervisor and Reviewers.

### **1. Reviewer Assessment (Total: 60 Marks)**

This is where the majority of your grade sits. These are the people who don’t know your "effort"—they only see your output.

- **Project Proposal:** 10 Marks
    
- **Review - I:** 20 Marks
    
- **Final Assessment:** 30 Marks
    

### **2. Supervisor Assessment (Total: 40 Marks)**

This covers your consistency and progress throughout the semester.

- **Review - I (Course Work):** 10 Marks
    
- **Review - II (Course Work):** 10 Marks
    
- **Final Review (Course Work):** 20 Marks

# [CP - Bachelor (Phase I) - Final (Reviewer) - 30 marks 2.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%201/CP%20-%20Bachelor%20(Phase%20I)%20-%20Final%20(External)%20-%2030%20marks%202.docx)
**Final Review – Phase I Rubric (Reviewer)**
## **1. Final Outcome / Prototype (5 Marks Total)**

### **Component A: Related Literature Study (2.5 Marks)**

- **Excellent (2.5):** Project very clearly studied related literature and recommends improvisation based on recent concepts/technologies.
    
- **Very Good (2.0):** Clearly studied literature and recommends improvisation.
    
- **Good (1.5):** Mostly studied literature and recommends improvisation.
    
- **Satisfactory (1.0):** Partially studied literature and recommends improvisation.
    
- **Poor (0.5):** Minimally studied literature.
    
- **Very Poor (0.25):** Did not study related literature.
    

### **Component B: Analysis (2.5 Marks)**

- **Excellent (2.5):** Clearly identified **functional, non-functional, and technical requirements**; fully developed meaningful design criteria.
    
- **Very Good (2.0):** Identified most requirements; almost developed meaningful design criteria.
    
- **Good (1.5):** Partially identified requirements and design criteria.
    
- **Satisfactory (1.0):** Minimally identified requirements and design criteria.
    
- **Poor (0.5):** Poorly identified requirements and design criteria.
    
- **Very Poor (0.25):** Did not identify requirements or design criteria.
    

---

## **2. Report (5 Marks Total)**

### **Component A: Overall Structure and Contents (2.5 Marks)**

- **Excellent (2.5):** Strictly followed specified structure; very clear, well-written, and includes **all required evidence**.
    
- **Very Good (2.0):** Closely followed structure; almost clear and has required evidence.
    
- **Good (1.5):** Partially followed structure; reasonably written with partial evidence.
    
- **Satisfactory (1.0):** Minimally followed structure; fractionally written with minimal evidence.
    
- **Poor (0.5):** Poorly followed structure; poorly written/poor evidence.
    
- **Very Poor (0.25):** Did not follow format; very poorly written.
    

### **Component B: Formatting, In-text Citation, and Referencing (2.5 Marks)**

- **Excellent (2.5):** Strictly adheres to formatting standards; **Minimum of 15 proper references** clearly pointed out in-text.
    
- **Very Good (2.0):** Clearly formatted; **Minimum of 12 proper references**.
    
- **Good (1.5):** Partially formatted; **Minimum of 10 proper references**.
    
- **Satisfactory (1.0):** Minimally formatted; **Minimum of 8 proper references**.
    
- **Poor (0.5):** Poorly formatted; **Minimum of 5 proper references**.
    
- **Very Poor (0.25):** No formatting and no references.
    

---

## **3. Project Design (10 Marks Total)**

### **Component A: Design Methodologies (5 Marks)**

- **Excellent (5.0):** Fully developed meaningful design criteria with **suitable methodologies** to achieve requirements.
    
- **Very Good (4.0):** Appreciable design criteria with suitable methodologies.
    
- **Good (3.0):** Basic design criteria and methodologies.
    
- **Satisfactory (2.0):** Satisfactory design criteria and methodologies.
    
- **Poor (1.0):** Minimum design and methodologies.
    
- **Very Poor (0.25):** Below average design and methodologies.
    

### **Component B: Low and High Level Project Design (5 Marks)**

- **Excellent (5.0):** Design is excellent and satisfies **state-of-the-art standards** and technologies.
    
- **Very Good (4.0):** Design is appreciable to state-of-the-art standards.
    
- **Good (3.0):** Design meets average expectations of state-of-the-art standards.
    
- **Satisfactory (2.0):** Design is satisfactory.
    
- **Poor (1.0):** Design is minimal.
    
- **Very Poor (0.25):** Design is not satisfactory.
    

---

## **4. Presentation (10 Marks Total)**

### **Component A: Presentation (5 Marks)**

- **Excellent (5.0):** Effectively communicated the project process **within the time limit**.
    
- **Very Good (4.0):** Clearly communicated within the time limit.
    
- **Good (3.0):** Partially communicated within the time limit.
    
- **Satisfactory (2.0):** Minimally communicated within the time limit.
    
- **Poor (1.0):** Poorly communicated.
    
- **Very Poor (0):** No presentation.
    

### **Component B: Individual Contribution and Viva (5 Marks)**

- **Excellent (5.0):** Precisely demonstrated product/program functionalities and **defended effectively**.
    
- **Very Good (4.0):** Clearly demonstrated and defended favorably.
    
- **Good (3.0):** Partially demonstrated and defended.
    
- **Satisfactory (2.0):** Minimally demonstrated and defended poorly.
    
- **Poor (1.0):** Poorly demonstrated; unable to defend.
    
- **Very Poor (0):** Not able to demonstrate or defend.

# [CP - Bachelor (Phase I) - Final (Supervisor) - 20 marks 2.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%201/CP%20-%20Bachelor%20(Phase%20I)%20-%20Final%20(Supervisor)%20-%2020%20marks%202.docx)
**Final Review – Phase I Rubric (Project Supervisor)**
## **1. Conduct and Management (5 Marks Total)**

### **Component A: Weekly Progress (2.5 Marks)**

- **Excellent (2.5):** Regularly attended and discussed tasks exactly as per the work plan.
    
- **Very Good (2.0):** Regularly attended; slight deviation from the work plan.
    
- **Good (1.5):** Mostly attended; slight deviation from the work plan.
    
- **Satisfactory (1.0):** Frequently attended; more deviation from the work plan.
    
- **Poor (0.5):** Sparingly attended; significant deviation from the work plan.
    
- **Very Poor (0.25):** Sparingly attended; complete deviation from the work plan.
    

### **Component B: Similarity with Tracing Sheet (TS) (2.5 Marks)**

- **Excellent (2.5):** Project completely follows the process mentioned in the tracing sheet.
    
- **Very Good (2.0):** Slight deviation from the tracing sheet process.
    
- **Good (1.5):** Partial deviation from the tracing sheet process.
    
- **Satisfactory (1.0):** Slightly resembles the tracing sheet process.
    
- **Poor (0.5):** Fractionally resembles the tracing sheet process.
    
- **Very Poor (0.25):** Does not resemble the tracing sheet process.
    

---

## **2. Project Design (5 Marks Total)**

### **Component A: Design Methodologies (2.5 Marks)**

- **Excellent (2.5):** Fully developed meaningful design criteria with suitable methodologies.
    
- **Very Good (2.0):** Appreciable design criteria and suitable methodologies.
    
- **Good (1.5):** Basic design criteria and methodologies.
    
- **Satisfactory (1.0):** Satisfactory design criteria and methodologies.
    
- **Poor (0.5):** Very basic design and methodologies.
    
- **Very Poor (0.25):** Below par/inadequate design and methodologies.
    

### **Component B: Low and High Level Project Design (2.5 Marks)**

- **Excellent (2.5):** Design is excellent and satisfies state-of-the-art standards/tech.
    
- **Very Good (2.0):** Design is appreciable to state-of-the-art standards.
    
- **Good (1.5):** Design meets average expectations of state-of-the-art standards.
    
- **Satisfactory (1.0):** Design is satisfactory.
    
- **Poor (0.5):** Design is minimal.
    
- **Very Poor (0.25):** Design is not satisfactory.
    

---

## **3. Project Management (5 Marks Total)**

### **Component A: Resource Management (2.5 Marks)**

- **Excellent (2.5):** Excellent usage of the **latest tools and technologies**.
    
- **Very Good (2.0):** Very good usage of latest tools/tech.
    
- **Good (1.5):** Good usage of latest tools/tech.
    
- **Satisfactory (1.0):** Satisfactory usage of latest tools/tech.
    
- **Poor (0.5):** Poor usage of latest tools/tech.
    
- **Very Poor (0.25):** Very poor usage of latest tools/tech.
    

### **Component B: Individual Contribution (2.5 Marks)**

- **Excellent (2.5):** Effectively completed all assigned tasks.
    
- **Very Good (2.0):** Completed most assigned tasks.
    
- **Good (1.5):** Partially completed assigned tasks.
    
- **Satisfactory (1.0):** Minimally completed assigned tasks.
    
- **Poor (0.5):** Poorly completed assigned tasks.
    
- **Very Poor (0.25):** Did not complete assigned tasks.
    

---

## **4. Report (5 Marks Total)**

### **Component A: Overall Structure and Contents (2.5 Marks)**

- **Excellent (2.5):** Strictly followed structure; clear, well-written; includes **all required evidence**.
    
- **Very Good (2.0):** Closely followed structure; almost clear; includes required evidence.
    
- **Good (1.5):** Partially followed structure; reasonably written; partial evidence.
    
- **Satisfactory (1.0):** Minimally followed structure; fractionally written; minimal evidence.
    
- **Poor (0.5):** Poorly followed structure; poor writing/evidence.
    
- **Very Poor (0.25):** Did not follow format; very poorly written.
    

### **Component B: Formatting, In-text Citation, and Referencing (2.5 Marks)**

- **Excellent (2.5):** Strict adherence to standards; **Minimum 15 proper references** cited throughout.
    
- **Very Good (2.0):** Clearly formatted; **Minimum 12 proper references**.
    
- **Good (1.5):** Partially formatted; **Minimum 10 proper references**.
    
- **Satisfactory (1.0):** Minimally formatted; **Minimum 8 proper references**.
    
- **Poor (0.5):** Poorly formatted; **Minimum 5 proper references**.
    
- **Very Poor (0.25):** No formatting and no references.

# [CP - Bachelor (Phase I) - Proposal (Reviewer) - 10 marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%201/CP%20-%20Bachelor%20(Phase%20I)%20-%20Proposal%20(External)%20-%2010%20marks.docx)
**Proposal – Phase I Rubric (Reviewer)**
## **1. Proposal Components (10 Marks Total)**

### **Component A: Scope, Problem Identification & Significance (2 Marks)**

- **Excellent (2.0):** Objectives, scope, and problem identification are "up to the mark" (flawless).
    
- **Very Good (1.75):** Objectives, scope, and problem identification are mentioned clearly.
    
- **Good (1.5):** Objectives, scope, and problem identification are mentioned clearly.
    
- **Satisfactory (1.0):** Objectives, scope, and problem identification are satisfactory.
    
- **Poor (0.5):** Objectives, scope, and problem identification are not satisfactory.
    
- **Very Poor (0.25):** Objectives, scope, and problem identification are mentioned poorly.
    

### **Component B: Technology & Innovation (2 Marks)**

- **Excellent (2.0):** **Latest technologies** and **highly innovative** ideas are used.
    
- **Very Good (1.75):** Latest technologies and innovative ideas are used.
    
- **Good (1.5):** Recent technologies and innovative ideas are used.
    
- **Satisfactory (1.0):** Recent technologies and fewer creative ideas are used.
    
- **Poor (0.5):** Old technologies and poor ideas are used.
    
- **Very Poor (0.25):** Old technologies and very poor ideas are used.
    

### **Component C: Methodology & Work Plan (2 Marks)**

- **Excellent (2.0):** **Professional way** of choosing methods; high degree of work plan quality.
    
- **Very Good (1.75):** Very good approach to choosing methods and work plan.
    
- **Good (1.5):** Good understanding of design methods and work plan.
    
- **Satisfactory (1.0):** Partial understanding of design methods and a good work plan.
    
- **Poor (0.5):** Minimal understanding of design methods and a poor work plan.
    
- **Very Poor (0.25):** Poor understanding of design methods and a poor work plan.
    

### **Component D: On-Time Submission (2 Marks)**

- **Excellent (2.0):** Submitted exactly on the mentioned deadline.
    
- **Very Good (1.75):** Submitted on the first day of the grace period.
    
- **Good (1.5):** Submitted on the second day of the grace period.
    
- **Satisfactory (1.0):** Submitted on the third day of the grace period.
    
- **Poor (0.5):** Submitted on the fourth day of the grace period.
    
- **Very Poor (0.25):** Submitted after the grace period.
    

### **Component E: Tracing Sheet (TS) Construction (2 Marks)**

- **Excellent (2.0):** TS construction is excellent; module descriptions and details are **100% clear**.
    
- **Very Good (1.75):** 100% of details and 80% of modules are identified clearly.
    
- **Good (1.5):** More than 50% of details and modules are identified clearly.
    
- **Satisfactory (1.0):** 25% to 50% of details and modules are identified clearly.
    
- **Poor (0.5):** Less than 25% of details and modules are identified clearly.
    
- **Very Poor (0.25):** Details are poorly stated; modules are not identified at all.

# [CP - Bachelor (Phase I) - Review I (Reviewer) - 20 marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%201/CP%20-%20Bachelor%20(Phase%20I)%20-%20Review%20I%20(External)%20-%2020%20marks.docx)
**Review – I – Phase I (Reviewer)**
## **1. Project Analysis (10 Marks Total)**

### **Component A: Introduction, Problem Statement & Significance (5 Marks)**

- **Excellent (5.0):** Flawless understanding of the existing system, problem statement, objectives, scope, and significance.
    
- **Very Good (4.0):** Very good understanding of the existing system and project fundamentals.
    
- **Good (3.0):** Good understanding of the existing system and project fundamentals.
    
- **Satisfactory (2.0):** Satisfactory understanding of the system and project scope.
    
- **Poor (1.0):** Poor understanding of the problem and system significance.
    
- **Very Poor (0.25):** Inadequate/very poor understanding of all core project elements.
    

### **Component B: Related Literature Study (5 Marks)**

- **Excellent (5.0):** Study of related literature is very clear; project recommends **improvisation** based on **recent**concepts or technologies.
    
- **Very Good (4.0):** Study is clear; recommends improvisation based on recent concepts/tech.
    
- **Good (3.0):** Study is mostly complete; recommends improvisation based on recent concepts/tech.
    
- **Satisfactory (2.0):** Study is partial; recommends improvisation.
    
- **Poor (1.0):** Study is minimal; little to no recommendation for improvisation.
    
- **Very Poor (0.25):** Did not study related literature; no recommendations made.
    

---

## **2. Presentation (10 Marks Total)**

### **Component A: Presentation & Viva (5 Marks)**

- **Excellent (5.0):** Effectively communicated the project overview **within the time limit** and was **very well defended**.
    
- **Very Good (4.0):** Clearly communicated within the time limit and defended favorably.
    
- **Good (3.0):** Partially communicated within the time limit and defended partially.
    
- **Satisfactory (2.0):** Minimally communicated and defended minimally.
    
- **Poor (1.0):** Poorly communicated and defended poorly.
    
- **Very Poor (0.25):** Did not communicate the overview; unable to defend.
    

### **Component B: Individual Contribution (5 Marks)**

- **Excellent (5.0):** Effectively completed the assigned task.
    
- **Very Good (4.0):** Most assigned tasks were completed.
    
- **Good (3.0):** Partially completed assigned tasks.
    
- **Satisfactory (2.0):** Minimally completed assigned tasks.
    
- **Poor (1.0):** Poorly completed assigned tasks.
    
- **Very Poor (0.25):** Did not complete assigned tasks.
  
# [CP - Bachelor (Phase I) - Review I (Supervisor) - 10 marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%201/CP%20-%20Bachelor%20(Phase%20I)%20-%20Review%20I%20(Supervisor)%20-%2010%20marks.docx)
**Review – I – Phase I (Project Supervisor)**
## **1. Conduct and Management (6 Marks Total)**

### **Component A: Project Plan (2 Marks)**

- **Excellent (2.0):** Professional approach throughout for planning and execution.
    
- **Very Good (1.75):** Very good approach; minor deficiencies in planning/execution.
    
- **Good (1.5):** Good approach; minor deficiencies in planning/execution.
    
- **Satisfactory (1.0):** Acceptable approach; areas of deficiency present.
    
- **Poor (0.5):** Passable approach; notable areas of deficiency.
    
- **Very Poor (0.25):** Unacceptable approach; significant deficiencies.
    

### **Component B: Attendance & Weekly Progress (2 Marks)**

- **Excellent (2.0):** Regularly attended and discussed tasks exactly as per the work plan.
    
- **Very Good (1.75):** Regularly attended; slight deviation from the work plan.
    
- **Good (1.5):** Mostly attended; slight deviation from the work plan.
    
- **Satisfactory (1.0):** Frequently attended; more deviation from the work plan.
    
- **Poor (0.5):** Sparingly attended; more deviation from the work plan.
    
- **Very Poor (0.25):** Sparingly attended; complete deviation from the work plan.
    

### **Component C: Similarity with Tracing Sheet (TS) (2 Marks)**

- **Excellent (2.0):** Project completely follows the process mentioned in the tracing sheet.
    
- **Very Good (1.75):** Slight deviation from the tracing sheet process.
    
- **Good (1.5):** Partial deviation from the tracing sheet process.
    
- **Satisfactory (1.0):** Slightly resembles the tracing sheet process.
    
- **Poor (0.5):** Fractionally resembles the tracing sheet process.
    
- **Very Poor (0.25):** Does not resemble the tracing sheet process.
    

---

## **2. Literature Review (2 Marks Total)**

### **Component A: Related Literature Review (2 Marks)**

- **Excellent (2.0):** Very clearly studied related literature; recommends improvisation based on recent concepts or technologies.
    
- **Very Good (1.75):** Clearly studied; recommends improvisation based on recent concepts/tech.
    
- **Good (1.5):** Mostly studied; recommends improvisation based on recent concepts/tech.
    
- **Satisfactory (1.0):** Partially studied; recommends improvisation.
    
- **Poor (0.5):** Minimally studied; minimal recommendations.
    
- **Very Poor (0.25):** Did not study related literature; no recommendations.
    

---

## **3. Report (2 Marks Total)**

### **Component A: Structure, Contents and Formatting (2 Marks)**

- **Excellent (2.0):** Strictly followed the specified structure; very good formatting; very well written.
    
- **Very Good (1.75):** Clearly followed the structure; very good formatting; well written.
    
- **Good (1.5):** Partially followed the structure; good formatting; good write-up.
    
- **Satisfactory (1.0):** Minimally followed the structure; satisfactory formatting; good write-up.
    
- **Poor (0.5):** Poorly followed the structure; poor formatting; poor write-up.
    
- **Very Poor (0.25):** Did not follow the structure; poor formatting; very poor write-up.

# [CP - Bachelor (Phase I) - Review II (Supervisor) - 10 marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%201/CP%20-%20Bachelor%20(Phase%20I)%20-%20Review%20II%20(Supervisor)%20-%2010%20marks.docx)
**Review – II – Phase I (Project Supervisor)**
## **1. Project Plan and Analysis - Incremental (5 Marks Total)**

### **Component A: Related Literature Review (2.5 Marks)**

- **Excellent (2.5):** Very clearly studied related literature; recommends improvisation based on recent concepts/technologies.
    
- **Very Good (2.0):** Clearly studied; recommends improvisation based on recent concepts/tech.
    
- **Good (1.5):** Mostly studied; recommends improvisation.
    
- **Satisfactory (1.0):** Partially studied; recommends improvisation.
    
- **Poor (0.5):** Minimally studied literature.
    
- **Very Poor (0.25):** Did not study related literature.
    

### **Component B: Analysis (2.5 Marks)**

- **Excellent (2.5):** Clearly identified **functional, non-functional, and technical requirements**; fully developed meaningful design criteria.
    
- **Very Good (2.0):** Identified most requirements; almost developed meaningful design criteria.
    
- **Good (1.5):** Partially identified requirements and design criteria.
    
- **Satisfactory (1.0):** Minimally identified requirements and design criteria.
    
- **Poor (0.5):** Poorly identified requirements; provided poor design criteria.
    
- **Very Poor (0.25):** Did not identify requirements or provide design criteria.
    

---

## **2. Project Design - Incremental (5 Marks Total)**

### **Component A: Design Methodologies (2.5 Marks)**

- **Excellent (2.5):** Fully developed meaningful design criteria with **suitable methodologies** to achieve requirements.
    
- **Very Good (2.0):** Appreciable design criteria with suitable methodologies.
    
- **Good (1.5):** Basic design criteria with basic methodologies.
    
- **Satisfactory (1.0):** Satisfactory design criteria and methodologies.
    
- **Poor (0.5):** Very basic design and methodologies.
    
- **Very Poor (0.25):** Below par/inadequate design and methodologies.
    

### **Component B: Low and High Level Project Design (2.5 Marks)**

- **Excellent (2.5):** Project design is excellent and satisfies **state-of-the-art standards** and technologies.
    
- **Very Good (2.0):** Design is appreciable to state-of-the-art standards.
    
- **Good (1.5):** Design meets average expectations of state-of-the-art standards.
    
- **Satisfactory (1.0):** Design is satisfactory.
    
- **Poor (0.5):** Design is minimal.
    
- **Very Poor (0.25):** Design is not satisfactory.

# **Phase 2:**

# [CP - Bachelor (Phase II) - Final (Reviewer) - 30 marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%202/CP%20-%20Bachelor%20(Phase%20II)%20-%20Final%20(External)%20-%2030%20marks.docx)
**Final Review – Phase II (Reviewer)**
## **1. Project Development and Implementation (15 Marks Total)**

### **Component A: User Interface / Topology Design (5 Marks)**

- **Excellent (5.0):** Implemented **all** user interfaces or network topologies exactly as specified in the tracing sheet.
    
- **Very Good (4.0):** Implemented most interfaces/topologies.
    
- **Good (3.0):** Implemented interfaces/topologies partially.
    
- **Satisfactory (2.0):** Implemented interfaces/topologies minimally.
    
- **Poor (1.0):** Poor implementation.
    
- **Very Poor (0.25):** Implementation did not meet the tracing sheet specifications.
    

### **Component B: Development and Implementation (5 Marks)**

- **Excellent (5.0):** Product/program meets **all** user specifications and requirements.
    
- **Very Good (4.0):** Meets most specifications.
    
- **Good (3.0):** Meets specifications partially.
    
- **Satisfactory (2.0):** Meets specifications minimally.
    
- **Poor (1.0):** Meets specifications poorly.
    
- **Very Poor (0.25):** Did not meet user specifications.
    

### **Component C: Validation and Testing (5 Marks)**

- **Excellent (5.0):** Fully ensured validation is complete; project is **thoroughly tested** and satisfies client needs.
    
- **Very Good (4.0):** Validation almost ensured; project is tested and almost satisfies client needs.
    
- **Good (3.0):** Partial validation and partial testing.
    
- **Satisfactory (2.0):** Minimal validation and testing.
    
- **Poor (1.0):** Poor validation and testing.
    
- **Very Poor (0):** No validation or testing performed.
    

---

## **2. Presentation (10 Marks Total)**

### **Component A: Oral Presentation (5 Marks)**

- **Excellent (5.0):** Effectively communicated the project process **within the time limit**.
    
- **Very Good (4.0):** Clearly communicated within the time limit.
    
- **Good (3.0):** Partially communicated within the time limit.
    
- **Satisfactory (2.0):** Minimally communicated within the time limit.
    
- **Poor (1.0):** Poorly communicated.
    
- **Very Poor (0):** No presentation.
    

### **Component B: Individual Contribution and Viva (5 Marks)**

- **Excellent (5.0):** Precisely demonstrated functionalities and **defended effectively**.
    
- **Very Good (4.0):** Clearly demonstrated and defended favorably.
    
- **Good (3.0):** Partially demonstrated and defended.
    
- **Satisfactory (2.0):** Minimally demonstrated and defended poorly.
    
- **Poor (1.0):** Poorly demonstrated; unable to defend.
    
- **Very Poor (0):** Unable to demonstrate or defend.
    

---

## **3. Report (5 Marks Total)**

### **Component A: Overall Structure and Contents (2.5 Marks)**

- **Excellent (2.5):** Strictly followed structure; very clear, well-written; includes **all required evidence**.
    
- **Very Good (2.0):** Closely followed structure; almost clear; includes required evidence.
    
- **Good (1.5):** Partially followed structure; reasonably written.
    
- **Satisfactory (1.0):** Minimally followed structure.
    
- **Poor (0.5):** Poorly followed structure.
    
- **Very Poor (0.25):** Did not follow format.
    

### **Component B: Formatting, In-text Citation, and Referencing (2.5 Marks)**

- **Excellent (2.5):** Strict adherence to formatting; **Minimum 15 proper references** cited throughout.
    
- **Very Good (2.0):** Clear formatting; **Minimum 12 references**.
    
- **Good (1.5):** Partial formatting; **Minimum 10 references**.
    
- **Satisfactory (1.0):** Minimal formatting; **Minimum 8 references**.
    
- **Poor (0.5):** Poor formatting; **Minimum 5 references**.
    
- **Very Poor (0):** No formatting or references.

# [CP - Bachelor (Phase II) - Final (Supervisor) - 20 marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%202/CP%20-%20Bachelor%20(Phase%20II)%20-%20Final%20(Supervisor)%20-%2020%20marks.docx)
**Final Review – Phase II (Project Supervisor)**

## **1. Conduct and Management (5 Marks Total)**

### **Component A: Weekly Progress (2.5 Marks)**

- **Excellent (2.5):** Regularly attended and discussed the task exactly as per the work plan.
    
- **Very Good (2.0):** Regularly attended; slight deviation from the work plan.
    
- **Good (1.5):** Mostly attended; slight deviation from the work plan.
    
- **Satisfactory (1.0):** Frequently attended; more deviation from the work plan.
    
- **Poor (0.5):** Sparingly attended; more deviation from the work plan.
    
- **Very Poor (0.25):** Sparingly attended; complete deviation from the work plan.
    

### **Component B: Similarity with Tracing Sheet (TS) (2.5 Marks)**

- **Excellent (2.5):** Project completely follows the process as mentioned in the tracing sheet.
    
- **Very Good (2.0):** Slight deviation from the tracing sheet process.
    
- **Good (1.5):** Partial deviation from the tracing sheet process.
    
- **Satisfactory (1.0):** Slightly resembles the tracing sheet process.
    
- **Poor (0.5):** Fractionally resembles the tracing sheet process.
    
- **Very Poor (0.25):** Does not resemble the tracing sheet process.
    

---

## **2. Project Development and Implementation (10 Marks Total)**

### **Component A: User Interface / Topology Design (2.5 Marks)**

- **Excellent (2.5):** Implemented **all** user interface/topologies mentioned in the tracing sheet.
    
- **Very Good (2.0):** Implemented most.
    
- **Good (1.5):** Implemented partially.
    
- **Satisfactory (1.0):** Implemented minimally.
    
- **Poor (0.5):** Poor implementation.
    
- **Very Poor (0.25):** Did not meet the tracing sheet specifications.
    

### **Component B: Development and Implementation (2.5 Marks)**

- **Excellent (2.5):** Product meets **all** users’ specifications and requirements.
    
- **Very Good (2.0):** Meets most requirements.
    
- **Good (1.5):** Partially meets requirements.
    
- **Satisfactory (1.0):** Minimally meets requirements.
    
- **Poor (0.5):** Poorly meets requirements.
    
- **Very Poor (0.25):** Did not meet requirements.
    

### **Component C: Validation and Testing (2.5 Marks)**

- **Excellent (2.5):** Completely ensured validation is done fully; project is tested and satisfies client needs.
    
- **Very Good (2.0):** Validation almost ensured; project tested; almost satisfies client.
    
- **Good (1.5):** Partially validated and tested.
    
- **Satisfactory (1.0):** Minimally validated and tested.
    
- **Poor (0.5):** Poorly validated and tested.
    
- **Very Poor (0.25):** No validation or testing performed.
    

### **Component D: Individual Contribution and Viva (2.5 Marks)**

- **Excellent (2.5):** Precisely demonstrated functionalities and defended effectively.
    
- **Very Good (2.0):** Clearly demonstrated and defended favorably.
    
- **Good (1.5):** Partially demonstrated and defended.
    
- **Satisfactory (1.0):** Minimally demonstrated and defended poorly.
    
- **Poor (0.5):** Poorly demonstrated; unable to defend.
    
- **Very Poor (0.25):** Not able to demonstrate or defend.
    

---

## **3. Report (5 Marks Total)**

### **Component A: Overall Structure and Contents (2.5 Marks)**

- **Excellent (2.5):** Strictly followed specified report structure; very clear, well-written; includes all required evidence.
    
- **Very Good (2.0):** Closely followed structure; almost clear; has required evidence.
    
- **Good (1.5):** Partially followed structure; reasonably written.
    
- **Satisfactory (1.0):** Minimally followed structure.
    
- **Poor (0.5):** Poorly followed structure.
    
- **Very Poor (0.25):** Did not follow format; very poorly written.
    

### **Component B: Formatting, In-text Citation, and Referencing (2.5 Marks)**

- **Excellent (2.5):** Strictly adheres to formatting standards; **Minimum 15 proper references** clearly pointed out.
    
- **Very Good (2.0):** Clearly formatted; **Minimum 12 references**.
    
- **Good (1.5):** Partially formatted; **Minimum 10 references**.
    
- **Satisfactory (1.0):** Minimally formatted; **Minimum 8 references**.
    
- **Poor (0.5):** Poorly formatted; **Minimum 5 references**.
    
- **Very Poor (0.25):** No formatting and references.

# [CP - Bachelor (Phase II) - Incorporating Changes from Phase I (Reviewer) - 5 Marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%202/CP%20-%20Bachelor%20(Phase%20II)%20-%20Incorporating%20Changes%20from%20Phase%20I%20(External)%20-%205%20Marks.docx)
**Rubrics for Incorporating Changes from Phase I – Phase II**
## **1. Design Changes from Phase I (5 Marks Total)**

### **Component: Design (5 Marks)**

This component evaluates how well you implemented the specific user interface, topology, and coding process changes suggested by the examiner at the end of Phase I.

- **Excellent (5.0):** Implemented **all** changes in user interface / topologies design and programming/coding process as suggested.
    
- **Very Good (4.0):** **80%** of suggested changes implemented.
    
- **Good (3.0):** **60%** of suggested changes implemented.
    
- **Satisfactory (2.0):** **40%** of suggested changes implemented.
    
- **Poor (1.0):** **20%** of suggested changes implemented.
    
- **Very Poor (0.25):** **No changes** implemented.

# [CP - Bachelor (Phase II) - Review I (Reviewer) - 20 marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%202/CP%20-%20Bachelor%20(Phase%20II)%20-%20Review%20I%20(External)%20-%2020%20marks.docx)
**Review – I – Phase II (Reviewer)**
## **1. Project Development and Implementation (10 Marks Total)**

### **Component A: User Interface / Topology Design (5 Marks)**

- **Excellent (5.0):** Implementation of **30%** user interface / topologies as mentioned in the tracing sheet.
    
- **Very Good (4.0):** Implementation of **25%**.
    
- **Good (3.0):** Implementation of **20%**.
    
- **Satisfactory (2.0):** Implementation of **15%**.
    
- **Poor (1.0):** Implementation of **10%**.
    
- **Very Poor (0.25):** Implementation of **5%**.
    

### **Component B: Programming / Coding (5 Marks)**

- **Excellent (5.0):** Implementation of **30%** process in programming/coding as mentioned in the tracing sheet.
    
- **Very Good (4.0):** Implementation of **25%**.
    
- **Good (3.0):** Implementation of **20%**.
    
- **Satisfactory (2.0):** Implementation of **15%**.
    
- **Poor (1.0):** Implementation of **10%**.
    
- **Very Poor (0.25):** Implementation of **5%**.
    

---

## **2. Presentation (10 Marks Total)**

### **Component A: Presentation & Viva (5 Marks)**

- **Excellent (5.0):** Effectively communicated the project process within the time limit and **very well defended**.
    
- **Very Good (4.0):** Clearly communicated within the time limit and defended favorably.
    
- **Good (3.0):** Partially communicated and defended partially.
    
- **Satisfactory (2.0):** Minimally communicated and defended minimally.
    
- **Poor (1.0):** Poorly communicated and defended poorly.
    
- **Very Poor (0.25):** Did not communicate and not able to defend.
    

### **Component B: Individual Contribution (5 Marks)**

- **Excellent (5.0):** Effectively completed the assigned task.
    
- **Very Good (4.0):** Most assigned tasks were completed.
    
- **Good (3.0):** Partially completed assigned tasks.
    
- **Satisfactory (2.0):** Minimally completed assigned tasks.
    
- **Poor (1.0):** Poorly completed assigned tasks.
    
- **Very Poor (0.25):** Not completed the assigned task.
  
# [CP - Bachelor (Phase II) - Review I (Supervisor) - 10 Marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%202/CP%20-%20Bachelor%20(Phase%20II)%20-%20Review%20I%20(Supervisor)%20-%2010%20Marks.docx)
**Review – I – Phase II (Project Supervisor)**
## **1. Conduct and Management (4 Marks Total)**

### **Component A: Attendance & Weekly Progress (2 Marks)**

- **Excellent (2.0):** Regularly attended and discussed the task as per the work plan.
    
- **Very Good (1.75):** Regularly attended; slight deviation from the work plan.
    
- **Good (1.5):** Mostly attended; slight deviation from the work plan.
    
- **Satisfactory (1.0):** Frequently attended; more deviation from the work plan.
    
- **Poor (0.5):** Sparingly attended; more deviation from the work plan.
    
- **Very Poor (0.25):** Sparingly attended; complete deviation from the work plan.
    

### **Component B: Similarity with Tracing Sheet (TS) (2 Marks)**

- **Excellent (2.0):** The project **completely follows** the process mentioned in the tracing sheet.
    
- **Very Good (1.75):** Slight deviation from the tracing sheet.
    
- **Good (1.5):** Partial deviation from the tracing sheet.
    
- **Satisfactory (1.0):** Slightly resembles the tracing sheet.
    
- **Poor (0.5):** Fractionally resembles the tracing sheet.
    
- **Very Poor (0.25):** Does not resemble the tracing sheet.
    

---

## **2. Project Development and Implementation (4 Marks Total)**

### **Component A: User Interface / Topology Design (2 Marks)**

- **Excellent (2.0):** Implementation of **30%** of UI/topologies as per the tracing sheet.
    
- **Very Good (1.75):** Implementation of **25%**.
    
- **Good (1.5):** Implementation of **20%**.
    
- **Satisfactory (1.0):** Implementation of **15%**.
    
- **Poor (0.5):** Implementation of **10%**.
    
- **Very Poor (0.25):** Implementation of **5%**.
    

### **Component B: Programming / Coding (2 Marks)**

- **Excellent (2.0):** Implementation of **30%** of programming/coding process as per the tracing sheet.
    
- **Very Good (1.75):** Implementation of **25%**.
    
- **Good (1.5):** Implementation of **20%**.
    
- **Satisfactory (1.0):** Implementation of **15%**.
    
- **Poor (0.5):** Implementation of **10%**.
    
- **Very Poor (0.25):** Implementation of **5%**.
    

---

## **3. Report (2 Marks Total)**

### **Component A: Structure, Contents, and Formatting (2 Marks)**

- **Excellent (2.0):** Strictly followed structure; very good formatting; very well written.
    
- **Very Good (1.75):** Clearly followed structure; very good formatting; well written.
    
- **Good (1.5):** Partially followed structure; good formatting and write-up.
    
- **Satisfactory (1.0):** Minimally followed structure; satisfactory formatting.
    
- **Poor (0.5):** Poorly followed structure and formatting.
    
- **Very Poor (0.25):** Did not follow structure; poor formatting; very poor write-up.
  

# [CP - Bachelor (Phase II) - Review II (Supervisor) - 10 marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%202/CP%20-%20Bachelor%20(Phase%20II)%20-%20Review%20II%20(Supervisor)%20-%2010%20marks.docx)
**Review – II – Phase II (Project Supervisor)**
## **1. Project Development and Implementation (10 Marks Total)**

### **Component A: User Interface / Topology Design (5 Marks)**

- **Excellent (5.0):** Implementation of **60%** user interface / topologies as mentioned in the tracing sheet.
    
- **Very Good (4.0):** Implementation of **50%**.
    
- **Good (3.0):** Implementation of **45%**.
    
- **Satisfactory (2.0):** Implementation of **40%**.
    
- **Poor (1.0):** Implementation of **35%**.
    
- **Very Poor (0.25):** Implementation of **30%**.
    

### **Component B: Programming / Coding (5 Marks)**

- **Excellent (5.0):** Implementation of **60%** process in programming/coding as mentioned in the tracing sheet.
    
- **Very Good (4.0):** Implementation of **50%**.
    
- **Good (3.0):** Implementation of **45%**.
    
- **Satisfactory (2.0):** Implementation of **40%**.
    
- **Poor (1.0):** Implementation of **35%**.
    
- **Very Poor (0.25):** Implementation of **30%**.
  

# [B.Tech -  Phase II - Poster Presentation - 5 Marks.docx](file:///Users/monta/Library/CloudStorage/GoogleDrive-almuntaser.alsarmi@gmail.com/My%20Drive/Clients/CP%20-%20AMS/Assessment%20Rubrics/Phase%202/B.Tech%20-%20%20Phase%20II%20-%20Poster%20Presentation%20-%205%20Marks.docx)
## **1. Design (3 Marks Total)**

This section evaluates the visual and structural quality of your poster.

- **Layout (0.5 Marks):** Must be neat and presentable.
    
- **Graphical Illustration (0.5 Marks):** Images/charts must enhance the meaning, not just be "fillers."
    
- **Creativity (0.5 Marks):** Originality in the design approach.
    
- **Linguistic/Grammatic Correctness (0.5 Marks):** Zero tolerance for typos or poor phrasing.
    
- **General Guidelines (1.0 Mark):** Strict adherence to poster making standards (font sizes, margins, etc.).
    

---

## **2. Research Contents (3 Marks Total)**

This is the "meat" of the poster. It must include, but is not limited to:

- **Questions & Objectives (0.5 Marks):** What are you trying to solve/achieve?
    
- **Relevance & Significance (0.5 Marks):** Why does this project matter?
    
- **Conceptual Framework (1.0 Mark):** Visual representation of your relevant designs and logic.
    
- **Analysis, Findings & Conclusion (1.0 Mark):** The core results of your initial study.
    

---

## **3. Presentation (3 Marks Total)**

This is your "live" performance during the poster session.

- **Q&A Performance (2.0 Marks):** Your ability to answer specific, often technical, questions from reviewers.
    
- **Flow (1.0 Mark):** A clear, continuous, and professional verbal delivery.
    

---

## **4. Timing (1 Mark Total)**

- **Adherence to Allotted Time (1.0 Mark):** If you go over your time limit, you lose 10% of this rubric’s total value immediately.

# Phase 2 – Consolidated Mark Statement (100 Marks)

## **Marking Breakdown (The Weights)**

The total assessment is out of **100 marks**, following the same consolidation logic as Phase 1.

### **1. Reviewer Assessment (Total: 60 Marks)**

- **Incorporating Changes from Phase I:** 5 Marks
- **Review - I:** 20 Marks
- **Final Assessment:** 30 Marks
- **Poster Presentation:** 5 Marks *(Rubric is scored out of 10, divided by 2 for consolidation)*

### **2. Supervisor Assessment (Total: 40 Marks)**

- **Review - I (Course Work):** 10 Marks
- **Review - II (Course Work):** 10 Marks
- **Final Review (Course Work):** 20 Marks

---



==Lets do not hardcode the process of evaluation, this way even if the process changed later or another institution want to use the service they can just create their own process.==

if we did this thing above we don't need to think about the process now, we need to just think about the engineering.



Entities:
Semester
users
projects


##### Claude questions:

🎯 Critical Clarifications Needed
1. Project & Student Structure
	∙	Is this individual or group projects? (1 student per project or multiple students per project?)
	Mainly this is a group project but sometimes there are cases where it has only one student. However the marking rubric will apply the same for groups and single students. We can  treat it as a group of one.
	
	∙	If groups: What’s the max group size? Do all members get the same marks or individual marks?
	There is no fixed amount for group members sometimes its 2 , sometimes it can got up to 4.
	But maybe we can make this option dynamic !
	
	∙	Can a student work on multiple projects in the same semester? (probably not, but confirm)
	No, students only work on one project only in the same semester.
	
1. Academic Structure
	∙	Do you need to track:
	∙	Department/College? (e.g., Engineering, IT)
	Yes
	
	∙	Program/Degree? (e.g., B.Tech Computer Science, B.Tech Information Security)
	Yes
	
	∙	Academic Year/Cohort? (2024-2025)
	Yes
	
	∙	Course codes? (e.g., PROJ4001)
	Yes
	


1. Reviewer Assignment & Types
From your rubrics, I see:
	∙	Reviewers (Reviewer I, Reviewer II)
	∙	Supervisors
	∙	Coordinators
	
Questions:
∙	==How are Reviewers assigned to projects? (Manual by coordinator?== 
yes Manual by coordinator
==∙	Can one person be both supervisor AND reviewer for different projects?==
yes he can, but supervisor can't review his own group that he is supervising. 
	
∙	How many reviewers per project? Always 2 reviewers ?
not always lets make it flexible we can assign as many as wanted

Are those reviewers will be the same throughout the project? In Phase 1 and phase 2
mainly the same, but it can be changed


==∙	Are supervisors assigned 1-to-1 with projects or can they supervise multiple?==
no, supervisors can  supervise more than one groupe , but groupes have only one supervisors.

1. ==Evaluation Process Flexibility ⚠️==
==You mentioned: “Let’s not hardcode the process of evaluation”==
==This is HUGE for database design. We need to decide:==
==Option A: Hardcoded (Simpler)==
	==∙	Fixed stages: Proposal → Review I → Review II → Final==
	==∙	Fixed evaluator types: Reviewer, Supervisor==
	==∙	Fixed rubrics per phase==
	
==Option B: Configurable (Complex but flexible)==
	==∙	Admins can create custom evaluation stages==
	==∙	Admins can define which roles evaluate at which stage==
	==∙	Admins can build custom rubrics with criteria==
==Which approach?==
we will go with option B 


2. Rubric & Criteria Structure
Looking at your rubric files:
	∙	Each phase has multiple assessment forms with different marks (10, 20, 30 marks)
	∙	Each form has criteria/sections
Questions:
	∙	Do we need to store the individual criterion scores or just the total per rubric?
	We need to store the individual criterion scores.
	∙	Example: If “Proposal (Reviewer) - 10 marks” has 5 criteria worth 2 marks each, do we store all 5 scores or just the total 10?
	We store the individual criterion scores, not only the total.
	∙	Can rubrics be reused across semesters or are they version-controlled?
	They can be, rubrics usually fixed. But if we are going with Option B (Configurable) we don’t need to worry about it right?
	
3. ==Student Submissions & Documents==
==You asked: “Students are going to upload their work to the system or reference from OneDrive?”==
For database schema, I need to know:
	∙	Do we store file metadata (filename, upload date, file path/URL)?
	No, we are not going to uplode files nither provide links, this system will only be concerned about assesments.
	∙	What documents exist: Proposal doc, Review I doc, Final report, presentation slides?
	dont worry about it will not be uploding it
	∙	Can students upload multiple versions? (Do we track revision history?)
4. Reports & Visibility
   we will not uplode any files
You asked: “System will generate reports for students only?”
Clarify what reports and who sees what:
	∙	Students: Can see their own marks? Consolidated final score only or breakdown?
	Students only see internal and consolidated marks only.
	∙	Supervisors: Can see all their supervised projects?
	yes
	∙	Coordinators: Can see all projects in their program/semester?
	yes
	∙	Reviewers: Can see only projects they’re assigned to?
	yes
	The same individual acting as a **Supervisor** may also serve as a **Reviewer** for a different project. The system must distinguish between these distinct **responsibilities** to ensure the user has full clarity regarding their specific **role**.
5. Workflow & Status Tracking
	∙	Do we need to track project status? (e.g., Proposal Submitted, Under Review, Approved, etc.)
	No,
	∙	Do we track deadlines for each stage?
	no
	∙	Can evaluators save marks as “draft” before final submission?
	yes
	==∙	Is there an approval process? (e.g., Coordinator approves consolidated marks?)==
	No
6. Consolidated Marks Calculation
From your documents, Phase I = 100 marks total:
	∙	Proposal (Reviewer): 10 marks
	∙	Review I (Reviewer): 20 marks
	∙	Review I (Supervisor): 10 marks
	∙	Review II (Supervisor): 10 marks
	∙	Final (Reviewer): 30 marks
	∙	Final (Supervisor): 20 marks
Questions:
	∙	Is this calculation formula fixed or configurable per semester/program?
	mainly its fixed, but lets keep since we are going with Option B: Configurable (Complex but flexible) we dont need to wory about it
	∙	Do we auto-calculate or allow manual override?
	we auto-calculate but also  allow manual override 
	∙	Do we need to store the formula/weights in the database?
	didn't really get this question
7. User Management Details
	∙	Can one user have multiple roles? (e.g., someone is both Supervisor and Coordinator?)
	yes
	∙	Do  reviewers need full system accounts or can they be invited per project?
	∙	Your question: “Can reviewers login to the system?”
	Yes, reviewers exist as user records and can log in to the system. They will fill the marking rubrics directly.
	
8. Comments & Feedback
	∙	Can evaluators leave text feedback/comments in addition to marks?
	yes
	∙	Are these comments visible to students?
	yes
	∙	Do supervisors need to review/moderate reviewer comments?
	no
9. Audit & History
You mentioned Spatie Activity Log for audit trails.
Confirm:
	∙	Do we need to track all mark changes (who changed what and when)?
	yes
	∙	Do we need version history for project documents?
	we will not be supporting project documents upload, so , no
	∙	Should deleted projects be soft-deleted or hard-deleted?
	


#### Gemini questions:
### 1. The "Dynamic Workflow" Reality Check

If you want to allow custom evaluation processes (Phase 1, Phase 2, different rubrics, different weights), you are no longer building a simple grading tool. You are building a **Form and Workflow Builder**.

- **The requirement:** We need tables for `Assessment_Templates`, `Rubric_Categories`, `Criteria`, `Scoring_Scales`, and `Workflow_Stages`.
    
- **The question:** How granular does this need to be? Do supervisors and reviewers use the _exact same_ criteria but with different weights, or entirely different rubrics per phase?
i will provide you with all the rubrics have currently, see by your self  

### 2. Ruthless Prioritization (Feature Creep)

You are floating ideas that distract from the core mission: securing and consolidating marks.

- **Public Evaluation Form:** _Kill this idea immediately._ It is out of scope, introduces unnecessary data pollution, creates security vulnerabilities, and serves no academic purpose for the core objectives. You are building an Assessment Management System, not a social feedback platform. 
agree, lets kill it 
    
- **Reviewers:** Yes, they must be treated as distinct users with strict Role-Based Access Control (RBAC). Reviewers exist as user records and can log in to the system to fill their marking rubrics directly.

### 3. Data Storage vs. Artifacts

- **Student Uploads vs. OneDrive Links:** You asked if students should upload files directly. _Do not host files if you don't have to._ File uploads introduce malware risks (requiring virus scanning), consume server storage, and complicate backups. Force students to submit a secure OneDrive URL. Store the _link_ as text in the database. It is cheaper, faster, and infinitely more secure.
- lets kill this idea also, we don't need it we have onedrive already.

### 4. Team Dynamics vs. Individual Projects

- **The Missing Link:** Is a "Course Project" assigned to a single student, or a group of students? If it's a group, do students get individual marks based on peer evaluations, or does the whole group get the exact same consolidated mark? This fundamentally changes the relationships between the `Users`, `Projects`, and `Assessments` tables.
No, the whole group does NOT get the exact same consolidated mark. and you can see this from the marking rubrics.

### 5. Reporting and Access

- **Reports for students only?** Absolutely not. The Coordinator needs an aggregated dashboard to see all projects and final grades. Supervisors need to see their specific supervises. Students should only see their final, authorized transcripts or feedback, depending on institutional policy.
yeah, Reports will not be for students only, Coordinator, Supervisors will have them also.

---

### The Architecture Interrogation

Before I architect this database schema, you must define the boundaries. Answer these five questions with absolute clarity:

1. **Project Ownership:** Can multiple students be assigned to a single project (Teams)? If yes, are grades applied to the team or the individual?
yes multiple students be assigned to a single project, and also a single project can have only one student assigned to it.
    
2. **Reviewer Access:** Will reviewers be given permanent credentials, or should the system generate temporary access links for their specific assigned reviews?
Reviewers exist as user records and can log in to the system. They fill the marking rubrics directly. Note that Reviewers may also be Supervisors for other projects.
    
3. **Rubric Flexibility:** Are we just allowing users to change the _weights_ of predefined criteria, or do they need to be able to create completely new criteria (e.g., "Presentation Skills", "Code Quality") from scratch in the UI?
let give them the option to be able to create completely new criteria from scratch in the UI.
    
4. **Submission Method:** Are we locking in the decision to use external URLs (OneDrive) for student submissions to avoid file storage security nightmares?
lets kill this idea, we dont need it we have onedrive alredy.
    
6. **Assessment Transparency:** When an assessment is done, does the student see the exact rubric breakdown from each reviewer, or just the final consolidated grade?
    Student only see internal and consolidated marks only, i think it will be good to be able to change what the student can see , to allow flexibility and allow adoption for the system. we need the system to be flexible and make it widely adaptable for different systems.



from the Meeting recordings:
1- the system will be generating reports for students and staff but each output will be different for each. like students.... they can see only the internal marks.

2- Reviewers exist as user records and can log in to fill their marking rubrics directly.

3- the system is only for assessments (marks), we are not uploading any work in it.

4- coordinator will assign the reviewers and supervisors manually

5- supervisors cannot be the reviewers of the same group they are supervising.



### 1. Data Retention Strategy: Soft Delete vs. Hard Delete

**Decision:** **Soft Delete**

- **The Reality:** In an academic environment, "I accidentally deleted that student's entire thesis record" is a nightmare you don't want to live through.
    
- **Implementation:** Every table will use a `deleted_at` timestamp. This preserves the audit trail and ensures that historical data remains intact for reporting, even if a record is "removed" from the active UI.
    

### 2. Definition of "Internal Marks"

**Decision:** **The Supervisor’s Grade**

- **The Reality:** In the 100-mark assessment battlefield, the "Internal" component is the domain of the Supervisor.
    
- **Structure:**
    
    - **Internal:** Marks awarded by the assigned Supervisor.
        
    - **Reviewers:** Marks awarded by the Panel of Reviewers.
        
    - **Total:** The consolidated sum of both. The student dashboard must clearly distinguish between these to manage expectations.
        

### 3. Development Scope

**Decision:** **Phase 1 + Phase 2 (Integrated)**

- **The Reality:** You are building for both from Day 1. Since the system is designed to be configurable, treating Phase 2 as a "future concern" is a luxury you can't afford. The architecture must support the full rubric lifecycle immediately to avoid a painful refactor in three months.
    

### 4. User Onboarding & Account Creation

**Decision:** **Manual Coordinator Control (CSV/Manual)**

- **The Reality:** Self-registration is a security liability you don't need. The Coordinator acts as the gatekeeper.
    
- **Workflow:**
    
    1. Coordinator manually creates individual accounts for edge cases.
        
    2. Coordinator performs bulk uploads via **CSV** for students and staff at the start of every semester.
        
    3. This ensures the system only contains verified users within the institutional domain.
        

### 5. Mark Integrity & Editing Permissions

**Decision:** **Locked on Submission with Coordinator Override**

- **The Reality:** A "Submit" button must mean something. Once a reviewer clicks it, the grade is finalized to prevent tampering or "grade drift."
    
- **The Fail-Safe:** Only the **Coordinator** has the authority to "Unlock" an assessment.
    
- **Audit Trail:** Every unlock and subsequent edit **must** be logged via `Spatie Activitylog` to maintain academic integrity and accountability.
    

### 6. Reporting & Output Formats

**Decision:** **On-screen Dashboards + Exportable PDFs**

- **The Reality:** Dashboards are for quick checks; PDFs are for the official record. You are committing to both.
    
- **Implementation:** High-fidelity on-screen views for daily management, with a standardized PDF generator for formal grade reports and transcripts.
    

### 7. Multi-Tenancy vs. Single-Institution

**Decision:** **Single-Institution (Self-Hosted)**

- **The Reality:** Do not over-engineer for a "SaaS" model you don't have yet. Every institution wanting the service will host and manage their own isolated database instance.
    
- **Strategic Benefit:** This simplifies the database schema (no global `institution_id` required) and significantly reduces security and data leakage risks between different schools.
  
  
  

***

### 1. The "Group vs. Individual" Grading Conundrum (CRITICAL)
You mentioned that projects are done in groups (1 to 4 students), but students receive individual marks based on "Individual Contribution" and "Viva" (as seen in the rubrics).
*   **What you need to figure out:** How will the UI and Database handle this? If a reviewer evaluates a group of 3 students, do they fill out **one** rubric for the shared project elements (e.g., *Literature Review, System Design*) and then fill out **three separate** sub-rubrics for the individual elements (e.g., *Presentation, Viva*)? 
*   **Why it matters:** Your database needs to map scores not just to a `Project`, but sometimes to a `Project` and sometimes to a specific `Student_ID`.
  
You cannot map every score blindly to a `Project_ID`, nor can you map every shared score (like the "Report") to three separate `Student_ID`s. If you do the latter, you create a data anomaly where Student A could accidentally get a 4/5 on the report while Student B gets a 5/5 _for the exact same shared report_.

You need a relational structure that separates the **Entity being graded** from the **Score**.

Here is your exact execution plan for the database:

1. **The Component Flag:** In your `Rubric_Criteria` table (which stores rows like "Literature Review", "Viva", etc.), you must add a boolean column: `is_individual` (True/False).
    
2. **The Unified Scoring Table:** Create an `Evaluation_Scores` table. It needs the following columns:
    
    - `Score_ID` (Primary Key)
        
    - `Evaluator_ID` (Who is giving the mark)
        
    - `Criterion_ID` (What is being graded)
        
    - `Project_ID` (Always required)
        
    - `Student_ID` (**Nullable** / Optional)
        
    - `Mark_Awarded`
        

**How it works:**

- When the examiner grades a **Group Component** (e.g., _System Design_ where `is_individual = False`), the system saves a single row with the `Project_ID` and leaves the `Student_ID` NULL. This applies the score to the whole team.
    
- When the examiner grades an **Individual Component** (e.g., _Viva_ where `is_individual = True`), the system saves three separate rows. Each row maps to the same `Project_ID`, but specifically populates the `Student_ID` for each individual.

### 2. User Onboarding & Authentication
You have the security stack defined (Laravel Fortify, MFA, Fail2Ban), but how do users actually get into the system?
*   **What you need to figure out:** Will you use the University's Single Sign-On (SSO) or will the Coordinator upload a CSV file of students and staff at the start of every semester? 
*   **Decision:** We will NOT use Microsoft SSO. The Coordinator will manage all user accounts — either manually via the UI or in bulk via CSV import. Passwords are managed locally via Laravel Fortify + MFA.
the Coordinator will create them manually or upload a CSV file of students and staff at the start of every semester
### 3. Data Export & SIS Integration
The system auto-calculates and centralizes the marks, but where do they go next?
*   **What you need to figure out:** Does the Coordinator need to export these final grades to the university's Student Information System (SIS) like Moodle, Banner, or Blackboard? 
*   **Recommendation:** At a minimum, plan for a "Generate CSV/Excel Export" feature for the Coordinator so they can easily upload the grades to the main university system.
  
  we will go with the provided Recommendation

### 4. Error Correction and "Unlocking" Grades
You mentioned evaluators can save marks as "Draft" before final submission.
*   **What you need to figure out:** What happens if a Supervisor or Reviewer hits "Final Submit" but realizes they made a mistake? Can they edit it? 
*   **Recommendation:** Once submitted, grades should be locked to enforce Integrity. Only a Coordinator should have the power to "Unlock" an assessment for a reviewer to edit, and this action MUST be logged in the Spatie Activitylog.

  we will go with the provided Recommendation
### 5. Semester Rollover (Lifecycle Management)
*   **What you need to figure out:** What happens when Fall 2026 ends and Spring 2027 begins? Do Coordinators have to rebuild all the dynamic rubrics from scratch?
*   **Recommendation:** You will need a "Clone Semester" or "Clone Rubric Template" feature so Coordinators can easily migrate their custom workflow setups to the next academic year without recreating them.

Cloning Semester is good, but what if we were having templates. Templates like Phase 1, Phase 2. These templates have the things like the marking rubric, etc.... already (things that are not always changing like students, semester, reviewers, etc... ) these marking rubric, etc... are not fixed it can be changed accordingly (they have their own templates) its like we are using templates inside a template.
i will tell you the process that i am imagining so you understand better:
lets start with the Coordinator (not a "super admin", they manage only the semesters they create), Coordinator will start with a fresh system no data on it, Coordinator can start with creating the rubrics, there will be like a folder thing where it shows all created rubrics like a file tree, this way Coordinator can create as many rubrics as he wants for test or new rubric for next semester, this will not affect the ongoing process because in order to use a rubric it needs to be added to be used (its like we are having a pool of rubrics that have old, new, experimental, for phase 1, for phase 2. all in one place and we need to just add it to use it in a phase), rubrics can be created in the system itself, in any way that they like with any criteria. we will also give the option to upload a rubric in a formatted format that our code will parse and make the rubric from it (this will give the Coordinator faster adding time instead of doing it manually), Coordinator also can add users in bulk from a csv file, also it can be added from the UI. Coordinator also creates a template of a phase then uses it and to use the same template he needs to just select one of the created templates and set it up by adding its details, creating groups and assigning students to it, and also we will give the option to create the groups and assign the students and their supervisors in bulk by just CSV file. this is some of the things that the Coordinator can do, i believe that there is more....
### 6. Managing the "Dual Role" UI
You noted: *"The same individual acting as a Supervisor may also serve as a Reviewer for a different project."*
*   **What you need to figure out:** How should this look in FilamentPHP? 
*   **Recommendation:** When Dr. Smith logs in, their dashboard should clearly split into two panels: **"Projects I am Supervising"** and **"Projects I am Reviewing"**. Do not mix them into one table, or they might accidentally apply the wrong rubric to the wrong group.

  we will go with the provided Recommendation
---

### What should be your immediate next steps?

1.  **Draft the Database Schema (ERD):** Because you are building a dynamic form builder, your schema needs to be solid. Focus on designing the relationships between `Semesters`, `Projects`, `Users`, `Evaluation_Stages` (Phase 1, Phase 2), `Rubric_Templates`, `Criteria`, and `Scores`.
2.  **Setup the Development Environment:** Install Laravel Herd, initialize Laravel 11, install FilamentPHP v5, and set up your MySQL database. 
3.  **Build the Coordinator Panel first:** Before you build the grading interface, build the UI where the Coordinator creates the dynamic rubrics and criteria. Coordinators are NOT super admins — they manage only the semesters they create. A separate Super Admin role (seeded at installation) is responsible for creating Coordinator accounts.
   

### 1. The "Template Pool" Management (System Setup)
Before any students or projects exist, the Coordinator sets up the rules of the game.
* **Manage Criteria:** They can create individual grading criteria (e.g., "Literature Review", "Viva"). Crucially, they set whether a criterion is a **Group mark** (`is_individual = false`) or an **Individual mark** (`is_individual = true`).
  
  okay this is good but also allow creation of Criteria inside the Rubric Templates themselves. it will be the same exactly but faster for the Coordinator to just create the criteria at that moment without the need of going out and creating Criteria then go to setup template again and add it. 
  note that, the Criteria that are created inside the Rubric Templates will not be saved automatically, unless the Coordinator saves it (there will be an option in the UI to do that)
  
* **Manage Rubric Templates:** They can bundle criteria together to create Rubrics (e.g., "Phase 1 - Reviewer Rubric"). They can build these manually via the UI or, eventually, upload them via a formatted CSV. These live in a "folder-like" pool and can be edited, versioned, or archived without affecting active semesters.
  
  regarding the formatted CSV, it is supposed to create the whole rubric with its Criteria. Only one rubric at a time.
  (Note: The exact CSV format/columns are TBD and will be decided later).
  
* **Manage Phase Templates:** They create a blueprint for a Phase (e.g., "B.Tech Phase 1"). Here, they map the rules: *"Supervisors must complete Rubric A, and Reviewers must complete Rubric B."*
  exactly its like a blueprint, it will have template name, rubrics and their fill order, rules.
  rubrics and their fill order:
  rubrics have to be done in order, these rubrics will be set and organized and the role of who is going to fill it will be defined also. Like for example Final Review – Phase II will come after we finish the second Review and it needs to be filled by the Project Supervisor, now this will automatically assign Final Review – Phase II for each Project Supervisor reducing need of manual allocating. And like this with the other things.
   rules:
   same things like who will fill what, other rules that i cant think of at the moment
   
   however the things that need to be set always are the things like the groups, project name, assigning students to the groups, assigning Supervisors and the reviewers. These can be all done fast by uploading a csv file in some format.
   
### 2. Semester & User Onboarding (Execution)
When a new academic term starts, the Coordinator brings the system to life.
* **Create Semesters:** They create a new entity (e.g., "Fall 2026"). Since Courses map to Phase Templates, Semester can contain Phase 1 and Phase 2 projects within the same term.
* **User Management (Bulk & Manual):** They can manually add users or **upload a CSV** to populate the system with Students, Supervisors, and Reviewers. 
* **Role Assignment:** They can assign multiple roles to a single user (e.g., Dr. Smith is given both the `Supervisor` and `Reviewer` roles).
* **Project & Group Allocation:** They can create Projects, assign 1-4 students to a project, assign **one** Supervisor, and assign **one or more** Reviewers to that project. *Crucially, they can do all of this in bulk via CSV upload to save time.*

### 3. Active Semester Monitoring & Moderation (The Sandbox)
While the semester is running and staff are grading, the Coordinator acts as the overseer.
* **Global Dashboard:** They can see the real-time status of all projects in their program/semester (e.g., *Which reviewers haven't submitted their marks yet?*). The auto-calculation of consolidated marks only fires when all attached evaluations for a project reach `submitted`. Project status progresses from `setup` → `evaluating` → `completed`.
* **"Unlock" Assessments:** If a Supervisor or Reviewer accidentally hits "Final Submit" and locks their grade, the Coordinator has the exclusive power to unlock it so the staff member can fix it. (This action is strictly logged in the Spatie Activitylog).
* **Manual Grade Overrides:** If the system calculates a final consolidated mark of 79, but the Coordinator needs to bump it to an 80, they can use the **Override Feature**. The system will keep the original `calculated_score`, save the new `override_score`, and require an `override_reason`.

### 4. End of Semester & Exporting (Output)
When all marks are finalized, the Coordinator handles the official outputs.
* **Generate PDF Reports:** They can generate official, consolidated grade reports for the academic records.
* **Export for SIS:** They can export the final grades as a CSV/Excel file to easily upload into the university's Student Information System (Moodle, Banner, etc.).
* **Clone for Next Semester:** When Spring 2027 rolls around, they don't have to start from scratch. They can just pull the existing Phase Templates and Rubrics from the "Pool" and start the process over again.

***
***
# **Entity-Relationship Diagram (ERD)**

> **Note:** All tables use Laravel's `SoftDeletes` trait (`deleted_at` column) and Laravel's default `timestamps()` (`created_at`, `updated_at` columns). This preserves audit trails and ensures historical data remains intact even when records are "removed" from the active UI.

> **Note:** The system ships with a **Super Admin** account seeded via `php artisan db:seed` (or a dedicated artisan command) during initial installation. The Super Admin is responsible for: **(1)** creating and managing **Coordinator** accounts, **(2)** managing master data (Departments, Specializations, Courses). Coordinators then manage their own semesters, users, rubrics, and projects. The Super Admin is NOT a Coordinator — it is a system-level bootstrap/admin role.

### Domain 1: Users, Access & Master Data
*(Note: User Roles and Permissions will be handled automatically by the `spatie/laravel-permission` package, which generates its own pivot tables. Roles include: `super_admin`, `coordinator`, `supervisor`, `reviewer`, `student`).*

| Table Name | Column Name | Data Type | Modifiers / Notes |
| :--- | :--- | :--- | :--- |
| **users** | `university_id` | String | Unique (e.g., Student ID or Staff ID) |
| | `name` | String | |
| | `email` | String | Unique |
| | `password` | String | Hashed. Managed locally via Laravel Fortify + MFA. |
| | `specialization_id`| Foreign ID | Nullable. Links to `specializations` (Useful for filtering/assigning groups). |
| **departments** | `name` | String | e.g., "Information Technology" |
| **specializations** | `department_id` | Foreign ID | Links to `departments` |
| | `name` | String | e.g., "Software Engineering" |
| **courses** | `code` | String | e.g., "IT4001" |
| | `title` | String | e.g., "B.Tech Project Phase I" |
| **grading_scales** | `min_score` | Decimal | Minimum percentage (e.g., 80.00) |
| | `max_score` | Decimal | Maximum percentage (e.g., 89.99) |
| | `letter_grade` | String | Letter matching the score (e.g., 'B+') |
| | `gpa_equivalent` | Decimal | GPA value for the grade (e.g., 3.5) |

---

### Domain 2: The Template Pool (The Workflow Engine)
*This is where the Coordinator builds the rubrics and defines the rules before a semester starts.*

| Table Name | Column Name | Data Type | Modifiers / Notes |
| :--- | :--- | :--- | :--- |
| **rubric_templates** | `name` | String | e.g., "Phase 1 Final Reviewer" |
| | `version` | Integer | Default: 1. Increments when a template is cloned/revised. |
| | `parent_template_id`| Foreign ID | Nullable. Links to `rubric_templates`. Points to the template this was cloned from (version lineage). NULL for originals. |
| | `total_marks` | Decimal | Calculated sum of criteria |
| | `is_locked` | Boolean | Default: False. Locks once used in evaluation. |
| | `created_by` | Foreign ID | Links to `users` (Coordinator who created this template) |
| **criteria** | `title` | String | e.g., "Literature Review" |
| | `description` | Text | Nullable (instructions for evaluator) |
| | `max_score` | Decimal | e.g., 2.5, 5.0, 10.0 |
| | `is_individual` | Boolean | **CRITICAL:** True = Score per student. False = Score per project. |
| | `rubric_template_id`| Foreign ID | Links to `rubric_templates` (1-to-Many to preserve history when cloning) |
| **score_levels** | `criterion_id` | Foreign ID | Links to `criteria` |
| *(Rating Scale)* | `label` | String | e.g., "Excellent", "Very Good", "Good", etc. |
| | `score_value` | Decimal | e.g., 5.0, 4.0, 3.0 — the point value for this level |
| | `description` | Text | Nullable. Descriptor text (e.g., "Precisely demonstrated functionalities and defended effectively") |
| | `sort_order` | Integer | Display ordering (1 = highest, 6 = lowest) |
| **phase_templates** | `name` | String | e.g., "B.Tech Phase I" |
| | `total_phase_marks`| Decimal | e.g., 100.00 |
| | `created_by` | Foreign ID | Links to `users` (Coordinator who created this template) |
| **phase_rubric_rules** | `phase_template_id` | Foreign ID | Links to `phase_templates` |
| *(Pivot Table)* | `rubric_template_id`| Foreign ID | Links to `rubric_templates` |
| | `evaluator_role` | String | Flexible role identifier (e.g., 'Supervisor', 'Reviewer', or institution-specific roles like 'External Examiner'). Not a hardcoded enum — allows the system to be adopted by different institutions without schema changes. |
| | `fill_order` | Integer | **Sequential Logic** (e.g., 1, 2, 3) |
| | `max_marks` | Decimal | Maximum marks for auto-calculation. Percentage is derived: `max_marks / total_phase_marks * 100`. |
| | `aggregation_method` | Enum | `AVERAGE`, `WEIGHTED_AVERAGE`, `SUM`, `MAX`. **Business Rule:** When multiple reviewers evaluate the same rubric, the system takes the AVERAGE by default. `WEIGHTED_AVERAGE` is available for institutions where certain evaluators carry more weight. If only one evaluator fills a rubric (e.g., Supervisor), no aggregation is needed. |

---

### Domain 3: Academic Setup (The Active Sandbox)
*Created by the Coordinator's Master CSV upload at the start of a term.*

| Table Name | Column Name | Data Type | Modifiers / Notes |
| :--- | :--- | :--- | :--- |
| **coordinator_semester** | `user_id` | Foreign ID | Links to `users` (Coordinator) |
| *(Pivot Table)* | `semester_id` | Foreign ID | Links to `semesters` |
| **semesters** | `name` | String | e.g., "Fall 2026" |
| | `academic_year` | String | e.g., "2025-2026" |
| | `start_date` | Date | Nullable. Semester start date for chronological sorting and filtering. |
| | `end_date` | Date | Nullable. Semester end date. |
| | `is_active` | Boolean | Default: True |
| | `is_closed` | Boolean | Default: False (Read-Only trigger) |
| **projects** | `semester_id` | Foreign ID | Links to `semesters` |
| | `course_id` | Foreign ID | Links to `courses` |
| | `phase_template_id`| Foreign ID | Links to `phase_templates`. **Per-project** (not per-course) so different semesters can use different templates for the same course. |
| | `specialization_id`| Foreign ID | Links to `specializations` |
| | `title` | String | The project name |
| | `supervisor_id` | Foreign ID | Links to `users` (The single Supervisor) |
| | `previous_phase_project_id` | Foreign ID | Nullable. Links to `projects` (Phase 1 to Phase 2 continuity) |
| | `status` | Enum | `setup`, `evaluating`, `completed` |
| **project_student** | `project_id` | Foreign ID | Pivot for 1-4 students per project |
| *(Pivot Table)* | `user_id` | Foreign ID | Links to `users` (Student) |
| **project_reviewer** | `project_id` | Foreign ID | Pivot for 1+ reviewers per project |
| *(Pivot Table)* | `user_id` | Foreign ID | Links to `users` (Reviewer) |

---

### Domain 4: Execution & Scoring (The Grading Interface)
*This tracks the actual drafting, submitting, and overriding of marks.*

| Table Name | Column Name | Data Type | Modifiers / Notes |
| :--- | :--- | :--- | :--- |
| **evaluations** | `project_id` | Foreign ID | Which project is being graded? |
| *(The Header)* | `rubric_template_id`| Foreign ID | Which rubric is being filled? |
| | `evaluator_id` | Foreign ID | Links to `users` (Who is grading?) |
| | `evaluator_role` | String | Flexible role identifier (e.g., 'Supervisor', 'Reviewer'). Matches `phase_rubric_rules.evaluator_role`. |
| | `on_behalf_of_user_id` | Foreign ID | Nullable. Links to `users`. **Day 1 Feature — Proxy Marking.** Only **Coordinators** are authorized to enter marks on behalf of an absent evaluator. When used, `evaluator_id` is the original assigned evaluator and `on_behalf_of_user_id` records the Coordinator who actually entered the marks. This action is logged via Spatie Activitylog. |
| | `evidence_attachment_path`| String | Nullable. Path to scanned physical rubric if proxy graded. |
| | `status` | Enum | `pending`, `draft`, `submitted` (Locks editability) |
| | `general_feedback` | Text | Nullable |
| | `unlocked_by` | Foreign ID | Nullable (Coordinator ID if fail-safe is used) |
| > **Constraint** | | | `UNIQUE` combination of `['project_id', 'rubric_template_id', 'evaluator_id']` |
| **evaluation_scores** | `evaluation_id` | Foreign ID | Links to `evaluations` |
| *(The Line Items)*| `criterion_id` | Foreign ID | Links to `criteria` |
| | `score_level_id` | Foreign ID | Nullable. Links to `score_levels` (specific rating scale level selected). |
| | `student_id` | Foreign ID | **Nullable.** Only populated if Criterion `is_individual = true`. |
| | `score_awarded` | Decimal | The actual mark given |
| | `feedback` | Text | Nullable. Per-criterion text feedback/comment visible to students. |
| **consolidated_marks**| `project_id` | Foreign ID | Links to `projects` |
| *(The Output)* | `phase_template_id` | Foreign ID | Links to `phase_templates` |
| | `student_id` | Foreign ID | Every student gets their own final row. |
| | `total_calculated_score`| Decimal | Read-only math output from the system. |
| | `override_score` | Decimal | Nullable. Coordinator's manual override. |
| | `override_reason` | Text | Required if `override_score` is not null. |
| **consolidated_mark_components** | `consolidated_mark_id` | Foreign ID | Links to `consolidated_marks` |
| *(Score Breakdown)* | `source_label` | String | e.g., "Supervisor", "Reviewer", or the rubric template name |
| | `score` | Decimal | The sub-total from that source (e.g., 35.0, 55.0) |

---

### Handling Retakes & Failures
If a student fails a phase and needs to redo it in a *new* semester, no special database changes are required. The system simply handles this by creating a **brand new project** in the new semester, linked to the retaken course. The historical "failed" project remains securely locked in the old semester for auditing.

---

### Email Notifications
The system will send **email notifications** to users for key events, including but not limited to:
* **Evaluators** (Supervisors/Reviewers): Notified when they are assigned to a new project, and when an assessment is unlocked for re-editing by a Coordinator.
* **Coordinators**: Notified when all evaluations for a project are submitted (ready for consolidation).
* **Students**: Notified when their consolidated marks are finalized and available for viewing.

Email delivery will be handled via Laravel's built-in Mail system (configured with SMTP or a mail driver like Mailgun/SES). Notification preferences and additional triggers can be refined during development.

---

### Future Enhancements
* *(Previously Proxy Marking Evidence. This feature is now integrated as a Day 1 requirement in the `evaluations` table).*