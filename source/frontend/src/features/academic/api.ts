import { apiRequest, ensureCsrfCookie } from '@/lib/api/client'

export type Overview={academic_years:number;classes:number;majors:number;subjects:number;teachers:number;students:number}
export type AcademicYear={id:number;name:string;starts_on:string;ends_on:string;is_active:boolean}
export type Semester={id:number;academic_year_id:number;name:string;number:number;starts_on:string;ends_on:string;is_active:boolean;academic_year?:{id:number;name:string}}
export type Major={id:number;code:string;name:string;description?:string|null;is_active:boolean}
export type Subject={id:number;code:string;name:string;category:string;description?:string|null;is_active:boolean}
export type ClassGroup={id:number;name:string;grade_level:number;capacity:number;is_active:boolean;academic_year_id:number;major_id?:number|null;homeroom_teacher_id?:number|null;academic_year?:{id:number;name:string};major?:{id:number;code:string;name:string};homeroom_teacher?:{id:number;name:string}}
export type Student={id:number;name:string;username:string;email:string;status:string;student_profile?:{nis:string;nisn?:string|null;gender?:string|null;phone?:string|null;parent_name?:string|null;parent_phone?:string|null}}
export type Teacher={id:number;name:string;username:string;email:string;status:string;teacher_profile?:{employee_no?:string|null;phone?:string|null;expertise?:string|null}}
export type Enrollment={id:number;class_group_id:number;student_id:number;status:string;class_group?:{id:number;name:string};student?:{id:number;name:string;username:string;email:string}}
export type TeachingAssignment={id:number;class_group_id:number;subject_id:number;teacher_id:number;semester_id:number;weekly_hours:number;class_group?:{id:number;name:string};subject?:{id:number;name:string;code:string};teacher?:{id:number;name:string};semester?:{id:number;name:string}}

type Data<T>={data:T}
async function mutate<T>(path:string,method:string,body?:unknown){await ensureCsrfCookie();return apiRequest<Data<T>>(path,{method,body:body?JSON.stringify(body):undefined})}
export const academicApi={
 overview:()=>apiRequest<Data<Overview>>('/admin/academic/overview'),
 years:()=>apiRequest<Data<AcademicYear[]>>('/admin/academic/academic-years'),
 semesters:()=>apiRequest<Data<Semester[]>>('/admin/academic/semesters'),
 majors:()=>apiRequest<Data<Major[]>>('/admin/academic/majors'),
 classes:()=>apiRequest<Data<ClassGroup[]>>('/admin/academic/classes'),
 subjects:()=>apiRequest<Data<Subject[]>>('/admin/academic/subjects'),
 students:(search='')=>apiRequest<Data<Student[]>>(`/admin/academic/students${search?`?search=${encodeURIComponent(search)}`:''}`),
 teachers:(search='')=>apiRequest<Data<Teacher[]>>(`/admin/academic/teachers${search?`?search=${encodeURIComponent(search)}`:''}`),
 enrollments:()=>apiRequest<Data<Enrollment[]>>('/admin/academic/enrollments'),
 assignments:()=>apiRequest<Data<TeachingAssignment[]>>('/admin/academic/teaching-assignments'),
 save:(resource:string,id:number|null,body:unknown)=>mutate<unknown>(`/admin/academic/${resource}${id?`/${id}`:''}`,id?'PUT':'POST',body),
 remove:async(resource:string,id:number)=>{await ensureCsrfCookie();return apiRequest(`/admin/academic/${resource}/${id}`,{method:'DELETE'})},
}
