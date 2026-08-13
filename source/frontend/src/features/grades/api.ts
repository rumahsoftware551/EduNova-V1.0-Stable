import { apiRequest, ensureCsrfCookie } from '@/lib/api/client'

export type GradeSettings={
  assignment_weight:number; quiz_weight:number; passing_grade:number;
  missing_as_zero:boolean; status:'draft'|'published'; published_at?:string|null
}
export type GradeStudent={
  student:{id:number;name:string;username:string;subtitle?:string|null}
  assignment_average:number|null; quiz_average:number|null; adjustment:number;
  adjustment_note?:string|null; final_score:number|null; letter_grade?:string|null;
  passed:boolean; material_progress:number; missing_count:number; risk_flags:string[]
}
export type Gradebook={
  classroom:{id:number;title:string;code?:string|null;subject?:{id:number;code:string;name:string};class_group?:{id:number;name:string};semester?:{id:number;name:string};teacher?:{id:number;name:string}}
  settings:GradeSettings
  assessments:{assignments:Array<{id:number;title:string;max_score:number;due_at?:string|null}>;quizzes:Array<{id:number;title:string;max_score:number;closes_at?:string|null}>}
  students:GradeStudent[]
  summary:{students:number;scored_students:number;class_average:number|null;pass_rate:number|null;at_risk:number;published_materials:number}
}
export type GradebookSummary={id:number;title:string;subject?:{id:number;code:string;name:string};class_group?:{id:number;name:string};settings:GradeSettings;summary:Gradebook['summary']}
export type StudentGradeSummary={id:number;title:string;subject?:{id:number;code:string;name:string};class_group?:{id:number;name:string};published:boolean;published_at?:string|null;final_grade?:{assignment_average:number|null;quiz_average:number|null;adjustment:number;final_score:number;letter_grade:string;result_status:string}|null}
export type StudentGradeDetail={classroom:Gradebook['classroom'];settings:GradeSettings;progress?:{assignment_average:number|null;quiz_average:number|null;material_progress:number;missing_count:number}|null;published:boolean;final_grade?:{assignment_average:number|null;quiz_average:number|null;adjustment:number;final_score:number;letter_grade:string;result_status:string;published_at?:string|null}|null}
export type AdminAnalytics={summary:{classrooms:number;published_gradebooks:number;published_students:number;school_average:number|null;pass_rate:number|null;at_risk:number};classes:Array<{id:number;title:string;subject?:{name:string;code:string};class_group?:{name:string};teacher?:{name:string};grade_status:string;summary:Gradebook['summary']}>}

type Data<T>={data:T}
async function json<T>(path:string,method:string,body?:unknown){await ensureCsrfCookie();return apiRequest<Data<T>>(path,{method,body:body?JSON.stringify(body):undefined})}

export const gradeApi={
 teacher:{
  list:()=>apiRequest<Data<GradebookSummary[]>>('/teacher/gradebooks'),
  get:(classroomId:number)=>apiRequest<Data<Gradebook>>(`/teacher/gradebooks/${classroomId}`),
  settings:(classroomId:number,body:{assignment_weight:number;quiz_weight:number;passing_grade:number;missing_as_zero:boolean})=>json<GradeSettings>(`/teacher/gradebooks/${classroomId}/settings`,'PUT',body),
  adjustment:(classroomId:number,studentId:number,body:{points:number;note?:string})=>json(`/teacher/gradebooks/${classroomId}/students/${studentId}/adjustment`,'PUT',body),
  publish:(classroomId:number)=>json<Gradebook>(`/teacher/gradebooks/${classroomId}/publish`,'POST',{}),
  unpublish:(classroomId:number)=>json<Gradebook>(`/teacher/gradebooks/${classroomId}/unpublish`,'POST',{}),
 },
 student:{
  list:()=>apiRequest<Data<StudentGradeSummary[]>>('/student/grades'),
  get:(classroomId:number)=>apiRequest<Data<StudentGradeDetail>>(`/student/grades/${classroomId}`),
 },
 admin:{
  overview:()=>apiRequest<Data<AdminAnalytics>>('/admin/academic/learning-analytics'),
 }
}
