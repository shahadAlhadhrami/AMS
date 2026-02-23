# Data Flow Diagram (DFD) for Assessment Management System (AMS)

This document maps out the Data Flow Diagram (DFD) for the AMS from Level 0 (Context) down to Level 1. 

## Level 0 Context Diagram
The Level 0 context diagram defines the boundary of the AMS system and its primary interactions with external entities.

```mermaid
flowchart LR
    %% Professional Styling
    classDef system fill:#2563eb,color:#fff,stroke:#1e40af,stroke-width:4px,font-weight:bold;
    classDef entity fill:#f8fafc,stroke:#475569,stroke-width:2px,color:#1e293b;

    %% Central System
    System[("Assessment Management System<br>(AMS Core)")]:::system

    %% External Entities (Left Side - Inputs)
    subgraph Inputs [Administrators & Staff]
        direction TB
        Coord([Coordinator]):::entity
        Sup([Supervisor]):::entity
        Rev([Reviewer]):::entity
    end

    %% External Entities (Right Side - Outputs/Consumers)
    subgraph Outputs [Beneficiaries & External Systems]
        direction TB
        Stu([Student]):::entity
        SIS([Student Info System]):::entity
    end

    %% Left Side Data Flows
    Coord -- "Master Data & Rubrics" --> System
    Coord -- "Project Allocations" --> System
    Sup -- "Internal Marks" --> System
    Rev -- "External Marks" --> System

    %% Right Side Data Flows
    System -- "Final Dashboard" --> Coord
    System -- "Supervisee Progress" --> Sup
    System -- "Final & Internal Marks" --> Stu
    System -- "Grade Export (CSV)" --> SIS

    %% Spacing adjustments
    Inputs ~~~ System ~~~ Outputs
```

---

## Level 1 DFD: Core Processes
The Level 1 DFD breaks down the main system into its distinct functional areas (processes) and shows how data moves between them and the core data stores.

### Legend
*   **External Entities:** Squared/Rounded nodes
*   **Processes (Functions):** Circles/Ovals
*   **Data Stores (Databases):** Cylinders

```mermaid
flowchart LR
    %% Styling
    classDef process fill:#eff6ff,stroke:#3b82f6,stroke-width:2px,color:#1e3a8a;
    classDef store fill:#fff7ed,stroke:#c2410c,stroke-width:2px,color:#7c2d12;
    classDef entity fill:#f8fafc,stroke:#475569,stroke-width:2px,color:#1e293b;

    %% External Entities
    Coord([Coordinator]):::entity
    Sup([Supervisor]):::entity
    Rev([Reviewer]):::entity
    Stu([Student]):::entity
    SIS([Student Info System]):::entity

    subgraph SetupPhase [1. System & Workflow Setup]
        direction LR
        P1((1.0 Manage Master Data)):::process
        P2((2.0 Build Workflow Rules)):::process
        P3((3.0 Configure Semester Sandbox)):::process
        D1[(D1: Master Data)]:::store
        D2[(D2: Template Pool)]:::store
    end

    subgraph ExecutionPhase [2. Assessment & Reporting]
        direction LR
        P4((4.0 Process Assessments)):::process
        P5((5.0 Consolidate Grades)):::process
        P6((6.0 Generate Reports & Export)):::process
        D3[(D3: Academic Sandbox)]:::store
        D4[(D5: Evaluations)]:::store
        D5[(D6: Consolidated Marks)]:::store
    end

    %% Flows for Setup
    Coord -- "Data" --> P1
    P1 --> D1
    Coord -- "Rubrics" --> P2
    P2 --> D2
    D1 -.-> P2
    Coord -- "Allocations" --> P3
    D1 -.-> P3
    D2 -.-> P3
    P3 --> D3

    %% Flows for Execution
    D3 -.-> P4
    D2 -.-> P4
    Sup -- "Marks" --> P4
    Rev -- "Marks" --> P4
    P4 --> D4
    D4 -.-> P5
    D3 -.-> P5
    P5 --> D5
    Coord -- "Overrides" --> P5
    D5 -.-> P6
    D4 -.-> P6
    P6 -- "Reports" --> Stu
    P6 -- "Exports" --> SIS
    P6 -- "Dashboards" --> Coord
```

### Detailed Breakdown of Level 1 Processes
1.  **1.0 Manage Master Data:** Coordinator handles users, courses, and baseline academic routing constraints. *(Uses: Users, Departments, Specializations tables).*
2.  **2.0 Build Workflow Rules:** Coordinator creates the Rubrics, Criteria, and Phase structure logic. This defines *how* a project will be marked and handles the "Is Individual vs Group" boolean logic. *(Uses: Template Pool Tables).*
3.  **3.0 Configure Semester Sandbox:** Coordinator formally binds students to projects and links Supervisors/Reviewers to the appropriate phase templates. This "activates" the grading system for those users. *(Uses: Semesters, Projects, Project_Student, Project_Reviewer).*
4.  **4.0 Process Assessments:** Supervisors and Reviewers actually fill out their dynamic rubrics. The system enforces the rule that evaluations move from 'Draft' to 'Submitted' (locked). *(Uses: Evaluations, Evaluation Scores).*
5.  **5.0 Consolidate Grades:** When all required evaluations for a project reach the 'Submitted' state, the system automatically pulls the logic built in Step 2.0 (like Weights/Averages) and crunches the math to calculate final Phase marks. Coordinators can also apply manual overrides here. *(Uses: Consolidated Marks).*
6.  **6.0 Generate Reports & Export:** Pulls the finalized grade data. Distributes the internal/final component marks dynamically so Students only see what they are allowed to see, provides dashboards to staff, and allows Coordinators to export CSVs for external SIS data entry.
