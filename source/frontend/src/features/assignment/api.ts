import { apiRequest, ensureCsrfCookie } from '@/lib/api/client'

export type AssignmentAttachment={id:number;assignment_id:number;original_name:string;mime_type?:string|null;file_size?:number|null;file_url?:string|null}
export type RubricCriterion={id:number;assignment_id:number;title:string;description?:string|null;max_points:number;position:number}
export type SubmissionFile={id:number;submission_id:number;original_name:string;mime_type?:string|null;file_size?:number|null;file_url?:string|null}
export type RubricScore={id:number;submission_id:number;rubric_criterion_id:number;points:number;comment?:string|null;criterion?:RubricCriterion}
export type AssignmentSubmission={id:number;assignment_id:number;student_id:number;status:'draft'|'submitted'|'late'|'graded'|'returned';text_answer?:string|null;link_url?:string|null;submitted_at?:string|null;is_late:boolean;attempt_no:number;score?:number|null;feedback?:string|null;graded_at?:string|null;returned_at?:string|null;student?:{id:number;name:string;username:string;subtitle?:string|null};files?:SubmissionFile[];rubric_scores?:RubricScore[];rubricScores?:RubricScore[];assignment?:Assignment}
export type Assignment={id:number;classroom_id:number;created_by:number;title:string;instructions?:string|null;opens_at?:string|null;due_at?:string|null;max_score:number;allow_text:boolean;allow_link:boolean;allow_file:boolean;allow_resubmit:boolean;max_file_mb:number;status:'draft'|'published'|'closed';published_at?:string|null;attachments?:AssignmentAttachment[];rubric_criteria?:RubricCriterion[];rubricCriteria?:RubricCriterion[];submissions_count?:number;pending_review_count?:number;is_overdue?:boolean;student_submission?:AssignmentSubmission|null;classroom?:{id:number;title:string;teaching_assignment?:{subject?:{id:number;code:string;name:string};class_group?:{id:number;name:string;grade_level:number};teacher?:{id:number;name:string}}}}

type Data<T>={data:T}
async function json<T>(path:string,method:string,body?:unknown){await ensureCsrfCookie();return apiRequest<Data<T>>(path,{method,body:body?JSON.stringify(body):undefined})}

export const assignmentApi={
 teacher:{
  list:()=>apiRequest<Data<Assignment[]>>('/teacher/assignments'),
  get:(id:number)=>apiRequest<Data<Assignment>>(`/teacher/assignments/${id}`),
  create:(classroomId:number,body:Partial<Assignment>)=>json<Assignment>(`/teacher/classrooms/${classroomId}/assignments`,'POST',body),
  update:(id:number,body:Partial<Assignment>)=>json<Assignment>(`/teacher/assignments/${id}`,'PUT',body),
  publish:(id:number,published:boolean)=>json<Assignment>(`/teacher/assignments/${id}/publish`,'POST',{published}),
  remove:async(id:number)=>{await ensureCsrfCookie();return apiRequest(`/teacher/assignments/${id}`,{method:'DELETE'})},
  addAttachment:async(id:number,file:File)=>{await ensureCsrfCookie();const form=new FormData();form.append('file',file);return apiRequest<Data<AssignmentAttachment>>(`/teacher/assignments/${id}/attachments`,{method:'POST',body:form})},
  removeAttachment:async(id:number)=>{await ensureCsrfCookie();return apiRequest(`/teacher/assignment-attachments/${id}`,{method:'DELETE'})},
  saveRubric:(id:number,criteria:Array<{title:string;description?:string;max_points:number}>)=>json<Assignment>(`/teacher/assignments/${id}/rubric`,'PUT',{criteria}),
  submissions:(id:number)=>apiRequest<Data<AssignmentSubmission[]>>(`/teacher/assignments/${id}/submissions`),
  submission:(id:number)=>apiRequest<Data<AssignmentSubmission>>(`/teacher/submissions/${id}`),
  grade:(id:number,body:{score?:number|null;feedback?:string;rubric_scores?:Array<{criterion_id:number;points:number;comment?:string}>})=>json<AssignmentSubmission>(`/teacher/submissions/${id}/grade`,'POST',body),
  returnToStudent:(id:number)=>json<AssignmentSubmission>(`/teacher/submissions/${id}/return`,'POST',{}),
 },
 student:{
  list:()=>apiRequest<Data<Assignment[]>>('/student/assignments'),
  get:(id:number)=>apiRequest<Data<Assignment>>(`/student/assignments/${id}`),
  save:async(id:number,body:{action:'draft'|'submit';text_answer?:string;link_url?:string;files?:File[]})=>{await ensureCsrfCookie();const form=new FormData();form.append('action',body.action);form.append('text_answer',body.text_answer??'');form.append('link_url',body.link_url??'');for(const file of body.files??[])form.append('files[]',file);return apiRequest<Data<AssignmentSubmission>>(`/student/assignments/${id}/submission`,{method:'POST',body:form})},
  removeFile:async(id:number)=>{await ensureCsrfCookie();return apiRequest(`/student/submission-files/${id}`,{method:'DELETE'})},
 }
}
