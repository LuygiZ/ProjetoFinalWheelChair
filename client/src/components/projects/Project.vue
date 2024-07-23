<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useRouter, onBeforeRouteLeave } from 'vue-router'

import { ref, watch, computed, onMounted} from 'vue'
import ProjectDetail from "./ProjectDetail.vue"

const toast = useToast()
const router = useRouter()

const newProject = () => { 
  return {
    id: null,
    name: '',
    responsible_id: 1,  // Change it later
    status: 'P',
    preview_start_date: null,
    preview_end_date: null,
    real_start_date: null,
    real_end_date: null,
    total_hours: null,
    billed: false,
    total_price: null,
  }
}

const project = ref(newProject())
const users = ref([])
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''

const loadProject = async (id) => {
    originalValueStr = ''
    errors.value = null
    if (!id || (id < 0)) {
      project.value = newProject()
      originalValueStr = JSON.stringify(project.value)
    } else {
      try {
        const response = await axios.get('projects/' + id)
        project.value = response.data.data
        originalValueStr = JSON.stringify(project.value)
      } catch (error) {
        console.log(error)
      }      
    }
  }

const save = async () => {
  errors.value = null
  if (operation.value == 'insert') {
    try {
      const response = await axios.post('projects', project.value)
      project.value = response.data.data
      originalValueStr = JSON.stringify(project.value)
      toast.success('Project #' + project.value.id + ' was created successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Project was not created due to validation errors!')
      } else {
        toast.error('Project was not created due to unknown server error!')
      }
    }
  } else {
    try {
      const response = await axios.put('projects/' + props.id, project.value)
      project.value = response.data.data
      originalValueStr = JSON.stringify(project.value)
      toast.success('Project #' + project.value.id + ' was updated successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Project #' + props.id + ' was not updated due to validation errors!')
      } else {
        toast.error('Project #' + props.id + ' was not updated due to unknown server error!')
      }
    }
  }
}    

const cancel = () => {
  originalValueStr = JSON.stringify(project.value)
  router.back()
}

const props = defineProps({
    id: {
      type: Number,
      default: null
    }
  })

const operation = computed(() => {
  return (!props.id || props.id < 0) ? 'insert' : 'update'
})

watch(
  () => props.id, 
  (newValue) => {
        loadProject(newValue)
  }, {
    immediate: true,
  }
)

let nextCallBack = null
const leaveConfirmed = () => {
  if (nextCallBack) {
    nextCallBack()
  }
}

onBeforeRouteLeave((to, from, next) => {
  nextCallBack = null
  let newValueStr = JSON.stringify(project.value)
  if (originalValueStr != newValueStr) {
    // Some value has changed - only leave after confirmation
    nextCallBack = next
    confirmationLeaveDialog.value.show()
  } else {
    // No value has changed, so we can leave the component without confirming
    next()
  }
}) 

onMounted(async () => {
  users.value = []
  try {
    const response = await axios.get('users')
    users.value = response.data.data
  } catch (error) {
    console.log(error)
  }
})
</script>


<template>
    <confirmation-dialog
      ref="confirmationLeaveDialog"
      confirmationBtn="Discard changes and leave"
      msg="Do you really want to leave? You have unsaved changes!"
      @confirmed="leaveConfirmed"
    >
    </confirmation-dialog>    
  <ProjectDetail
    :operationType="operation"
    :project="project"
    :users="users"
    :errors="errors"
    @save="save"
    @cancel="cancel"
  ></ProjectDetail>
</template>
