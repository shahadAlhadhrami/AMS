# Data Flow Diagram (DFD) for Assessment Management System (AMS)

This document maps out the Data Flow Diagram (DFD) for the AMS from Level 0 (Context) down to Level 1. 

## Level 0 Context Diagram
The Level 0 context diagram defines the boundary of the AMS system and its primary interactions with external entities.

```mermaid
flowchart TD
    %% External Entities
    Coord([Coordinator])
    Sup([Supervisor])
    Rev([Reviewer])
    Stu([Student])
    SIS([Student Information System / External])

    %% Main System Process
    System[Assessment Management System - AMS]

    %% Data Flows (Coordinator)
    Coord -- "Master Data (Users, Courses)" --> System
    Coord -- "Workflow Rules (Phase Templates, Rubrics)" --> System
    Coord -- "Semester/Project Allocations" --> System
    Coord -- "Manual Override Scores" --> System
    System -- "System Reports & Dashboard Data" --> Coord

    %% Data Flows (Supervisor)
    Sup -- "Internal Marks (Group/Individual)" --> System
    Sup -- "Proxy Evidence (If Needed)" --> System
    System -- "Assigned Projects & Assessment Status" --> Sup
    System -- "Consolidated Marks for Supervisees" --> Sup

    %% Data Flows (Reviewer)
    Rev -- "External Marks (Group/Individual)" --> System
    System -- "Assigned Review Projects & Rubrics" --> Rev

    %% Data Flows (Student)
    System -- "Final Consolidated & Internal Marks" --> Stu
    System -- "General Feedback" --> Stu

    %% Data Flows (SIS)
    System -- "Final Grades Export (CSV)" --> SIS
```

---

## Level 1 DFD: Core Processes
The Level 1 DFD breaks down the main system into its distinct functional areas (processes) and shows how data moves between them and the core data stores.

### Legend
*   **External Entities:** Squared/Rounded nodes
*   **Processes (Functions):** Circles/Ovals
*   **Data Stores (Databases):** Cylinders

```mermaid
flowchart TD
    %% External Entities
    Coord([Coordinator])
    Sup([Supervisor])
    Rev([Reviewer])
    Stu([Student])
    SIS([Student Info System])

    %% Data Stores
    D1[(D1: Master Data)]
    D2[(D2: Template Pool)]
    D3[(D3: Academic Sandbox)]
    D4[(D4: Evaluations)]
    D5[(D5: Consolidated Marks)]

    %% Processes
    P1((1.0 Manage<br>Master Data))
    P2((2.0 Build<br>Workflow Rules))
    P3((3.0 Configure<br>Semester Sandbox))
    P4((4.0 Process<br>Assessments))
    P5((5.0 Consolidate<br>Grades))
    P6((6.0 Generate<br>Reports & Export))

    %% Flows for P1 (Manage Master Data)
    Coord -- "Upload/Edit Users, Specializations" --> P1
    P1 -- "Store Core Entities" --> D1

    %% Flows for P2 (Build Workflow Rules)
    Coord -- "Create Criteria, Rubrics, Phase logic" --> P2
    P2 -- "Save Templates" --> D2
    D1 -. "Validate Roles" .-> P2

    %% Flows for P3 (Configure Semester Sandbox)
    Coord -- "Define Semester, Upload Project Allocations" --> P3
    D1 -. "Fetch Students/Staff" .-> P3
    D2 -. "Fetch Form Rules" .-> P3
    P3 -- "Save Active Project Groupings" --> D3

    %% Flows for P4 (Process Assessments)
    D3 -. "Fetch Active Project State" .-> P4
    D2 -. "Fetch Required Rubric Form" .-> P4
    Sup -- "Submit Supervisor Rubrics" --> P4
    Rev -- "Submit Reviewer Rubrics" --> P4
    Coord -- "Unlock Assessment Request" --> P4
    P4 -- "Store Scores & Feedback" --> D4

    %% Flows for P5 (Consolidate Grades)
    D4 -. "Read Completed Evaluations" .-> P5
    D3 -. "Fetch Phase Logic" .-> P5
    P5 -- "Calculate Auto-Math & Overrides" --> D5
    Coord -- "Override Request" --> P5

    %% Flows for P6 (Generate Reports)
    D5 -. "Read Final Scores" .-> P6
    D4 -. "Read Component Scores" .-> P6
    P6 -- "Display Allowed Marks" --> Stu
    P6 -- "Export File" --> SIS
    P6 -- "Dashboards" --> Coord
    P6 -- "Supervisee Dashboards" --> Sup
```

### Detailed Breakdown of Level 1 Processes
1.  **1.0 Manage Master Data:** Coordinator handles users, courses, and baseline academic routing constraints. *(Uses: Users, Departments, Specializations tables).*
2.  **2.0 Build Workflow Rules:** Coordinator creates the Rubrics, Criteria, and Phase structure logic. This defines *how* a project will be marked and handles the "Is Individual vs Group" boolean logic. *(Uses: Template Pool Tables).*
3.  **3.0 Configure Semester Sandbox:** Coordinator formally binds students to projects and links Supervisors/Reviewers to the appropriate phase templates. This "activates" the grading system for those users. *(Uses: Semesters, Projects, Project_Student, Project_Reviewer).*
4.  **4.0 Process Assessments:** Supervisors and Reviewers actually fill out their dynamic rubrics. The system enforces the rule that evaluations move from 'Draft' to 'Submitted' (locked). *(Uses: Evaluations, Evaluation Scores).*
5.  **5.0 Consolidate Grades:** When all required evaluations for a project reach the 'Submitted' state, the system automatically pulls the logic built in Step 2.0 (like Weights/Averages) and crunches the math to calculate final Phase marks. Coordinators can also apply manual overrides here. *(Uses: Consolidated Marks).*
6.  **6.0 Generate Reports & Export:** Pulls the finalized grade data. Distributes the internal/final component marks dynamically so Students only see what they are allowed to see, provides dashboards to staff, and allows Coordinators to export CSVs for external SIS data entry.
