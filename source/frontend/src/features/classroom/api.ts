import { apiRequest, ensureCsrfCookie } from '@/lib/api/client'

export type AssignmentMeta = {
  id:number; weekly_hours:number;
  subject?:{id:number;code:string;name:string}; class_group?:{id:number;name:string;grade_level:number};
  semester?:{id:number;name:string}; classroom?:{id:number;teaching_assignment_id:number;title:string;status:string}|null
}

export type MaterialProgress = {status:string;progress_percent:number;last_position_seconds:number;completed_at?:string|null}
export type LearningMaterial = {
  id:number; course_section_id:number; title:string; type:'video'|'pdf'|'link'|'text'|'file'; summary?:string|null;
  content?:string|null; external_url?:string|null; file_url?:string|null; original_name?:string|null; mime_type?:string|null;
  file_size?:number|null; duration_minutes?:number|null; position:number; is_preview:boolean; is_published:boolean;
  student_progress?:MaterialProgress|null
}
export type CourseSection = {id:number;classroom_id:number;title:string;description?:string|null;position:number;is_published:boolean;materials:LearningMaterial[]}
export type Classroom = {
  id:number; school_id:number; teaching_assignment_id:number; title:string; code?:string|null; description?:string|null;
  status:'draft'|'published'|'archived'; published_at?:string|null; sections_count?:number;materials_count?:number;
  completed_materials_count?:number;progress_percent?:number;
  teaching_assignment?:{id:number;subject?:{id:number;code:string;name:string};class_group?:{id:number;name:string;grade_level:number};teacher?:{id:number;name:string};semester?:{id:number;name:string}};
  sections?:CourseSection[]
}

type Data<T>={data:T}
async function jsonMutate<T>(path:string,method:string,body?:unknown){await ensureCsrfCookie();return apiRequest<Data<T>>(path,{method,body:body?JSON.stringify(body):undefined})}

export const classroomApi={
  teacher:{
    list:()=>apiRequest<Data<Classroom[]>>('/teacher/classrooms'),
    meta:()=>apiRequest<Data<AssignmentMeta[]>>('/teacher/classrooms/meta'),
    get:(id:number)=>apiRequest<Data<Classroom>>(`/teacher/classrooms/${id}`),
    create:(body:{teaching_assignment_id:number;title:string;description?:string})=>jsonMutate<Classroom>('/teacher/classrooms','POST',body),
    update:(id:number,body:{title:string;description?:string})=>jsonMutate<Classroom>(`/teacher/classrooms/${id}`,'PUT',body),
    publish:(id:number,published:boolean)=>jsonMutate<Classroom>(`/teacher/classrooms/${id}/publish`,'POST',{published}),
    remove:async(id:number)=>{await ensureCsrfCookie();return apiRequest(`/teacher/classrooms/${id}`,{method:'DELETE'})},
    addSection:(classroomId:number,body:{title:string;description?:string;is_published?:boolean})=>jsonMutate<CourseSection>(`/teacher/classrooms/${classroomId}/sections`,'POST',body),
    updateSection:(id:number,body:{title?:string;description?:string;is_published?:boolean})=>jsonMutate<CourseSection>(`/teacher/sections/${id}`,'PUT',body),
    removeSection:async(id:number)=>{await ensureCsrfCookie();return apiRequest(`/teacher/sections/${id}`,{method:'DELETE'})},
    saveMaterial:async(sectionId:number,id:number|null,body:{title:string;type:string;summary?:string;content?:string;external_url?:string;duration_minutes?:number;is_preview?:boolean;is_published?:boolean;file?:File|null})=>{
      await ensureCsrfCookie(); const form=new FormData();
      form.append('title',body.title);form.append('type',body.type);form.append('summary',body.summary??'');form.append('content',body.content??'');form.append('external_url',body.external_url??'');
      if(body.duration_minutes)form.append('duration_minutes',String(body.duration_minutes));form.append('is_preview',body.is_preview?'1':'0');form.append('is_published',body.is_published?'1':'0');if(body.file)form.append('file',body.file)
      return apiRequest<Data<LearningMaterial>>(id?`/teacher/materials/${id}`:`/teacher/sections/${sectionId}/materials`,{method:'POST',body:form})
    },
    publishMaterial:(id:number,published:boolean)=>jsonMutate<LearningMaterial>(`/teacher/materials/${id}/publish`,'POST',{published}),
    removeMaterial:async(id:number)=>{await ensureCsrfCookie();return apiRequest(`/teacher/materials/${id}`,{method:'DELETE'})},
  },
  student:{
    list:()=>apiRequest<Data<Classroom[]>>('/student/classrooms'),
    get:(id:number)=>apiRequest<Data<Classroom>>(`/student/classrooms/${id}`),
    material:(id:number)=>apiRequest<Data<{material:LearningMaterial&{section?:CourseSection};progress:MaterialProgress|null;navigation:{previous:{id:number;title:string}|null;next:{id:number;title:string}|null}}>>(`/student/materials/${id}`),
    progress:(id:number,body:{progress_percent?:number;last_position_seconds?:number;completed?:boolean})=>jsonMutate<MaterialProgress>(`/student/materials/${id}/progress`,'POST',body),
  }
}
